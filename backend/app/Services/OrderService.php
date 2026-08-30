<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Get customer orders
     */
    public function getCustomerOrders(User $user, ?string $status = null)
    {
        $query = Order::with(['subOrders.store', 'subOrders.items', 'payment'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(10);
    }

    /**
     * Get order detail by order number
     */
    public function getOrderByNumber(string $orderNumber, User $user): ?Order
    {
        return Order::with([
            'subOrders.store',
            'subOrders.items.variant.product',
            'subOrders.shipment.trackingEvents',
            'payment',
            'shippingAddress',
        ])
        ->where('order_number', $orderNumber)
        ->where('user_id', $user->id)
        ->first();
    }

    /**
     * Get seller sub-orders
     */
    public function getSellerSubOrders(User $user, ?string $status = null)
    {
        $query = SubOrder::with(['order.user', 'items.variant.product', 'store'])
            ->whereHas('store', fn($q) => $q->where('seller_id', $user->seller?->id))
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(10);
    }

    /**
     * Confirm a sub-order (seller action)
     */
    public function confirmSubOrder(SubOrder $subOrder): SubOrder
    {
        if (!$subOrder->canTransitionTo('confirmed')) {
            throw new \Exception('Cannot confirm this sub-order from current status.');
        }

        $subOrder->update(['status' => 'confirmed']);

        // Sync parent order status
        $this->syncOrderStatus($subOrder->order);

        // Notify customer
        \App\Models\Notification::createForUser(
            $subOrder->order->user,
            'order_confirmed',
            'Order Confirmed',
            "Your order #{$subOrder->order->order_number} from {$subOrder->store->name} has been confirmed.",
            ['order_id' => $subOrder->order_id, 'sub_order_id' => $subOrder->id]
        );

        return $subOrder;
    }

    /**
     * Ship a sub-order (seller action)
     */
    public function shipSubOrder(SubOrder $subOrder, string $courier, string $trackingNumber): SubOrder
    {
        if (!$subOrder->canTransitionTo('shipped')) {
            throw new \Exception('Cannot ship this sub-order from current status.');
        }

        $subOrder->update(['status' => 'shipped']);

        // Create shipment
        $shipment = $subOrder->shipment()->create([
            'courier' => $courier,
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'shipped_at' => now(),
        ]);

        // Sync parent order status
        $this->syncOrderStatus($subOrder->order);

        // Notify customer
        \App\Models\Notification::createForUser(
            $subOrder->order->user,
            'order_shipped',
            'Order Shipped',
            "Your order #{$subOrder->order->order_number} from {$subOrder->store->name} has been shipped. Tracking: {$trackingNumber}",
            ['order_id' => $subOrder->order_id, 'sub_order_id' => $subOrder->id, 'tracking_number' => $trackingNumber]
        );

        return $subOrder;
    }

    /**
     * Cancel a sub-order (seller action)
     */
    public function cancelSubOrder(SubOrder $subOrder, string $reason): SubOrder
    {
        if (!$subOrder->canTransitionTo('cancelled')) {
            throw new \Exception('Cannot cancel this sub-order from current status.');
        }

        DB::transaction(function () use ($subOrder, $reason) {
            $subOrder->update([
                'status' => 'cancelled',
                'cancelled_reason' => $reason,
            ]);

            // Release reserved stock
            foreach ($subOrder->items as $item) {
                $item->variant->inventory->release($item->quantity);
            }

            // Notify customer
            \App\Models\Notification::createForUser(
                $subOrder->order->user,
                'order_cancelled',
                'Order Cancelled',
                "Your order #{$subOrder->order->order_number} from {$subOrder->store->name} has been cancelled. Reason: {$reason}",
                ['order_id' => $subOrder->order_id, 'sub_order_id' => $subOrder->id]
            );
        });

        return $subOrder;
    }

    /**
     * Customer confirms receipt of order
     */
    public function completeOrder(Order $order): Order
    {
        if (!in_array($order->status, ['paid', 'processing', 'shipped'])) {
            throw new \Exception('Cannot complete this order from current status.');
        }

        // Check if all sub-orders are shipped or completed
        $allCompleted = $order->subOrders->every(fn($so) => $so->status === 'shipped' || $so->status === 'completed');

        if (!$allCompleted) {
            throw new \Exception('All sub-orders must be shipped before completing the order.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->subOrders as $subOrder) {
                if ($subOrder->status === 'shipped') {
                    $subOrder->update(['status' => 'completed']);

                    // Update store rating
                    $this->updateStoreRating($subOrder->store);

                    // Calculate and record commission
                    // Commission is calculated when sub-order is completed
                }
            }

            $order->update(['status' => 'completed']);
        });

        return $order;
    }

    /**
     * Sync parent Order status based on all sub-orders
     */
    protected function syncOrderStatus(\App\Models\Order $order): void
    {
        $order->load('subOrders');
        $statuses = $order->subOrders->pluck('status')->unique()->values();

        // Determine the most advanced status across all sub-orders
        $orderStatus = match(true) {
            $statuses->every(fn($s) => $s === 'completed') => 'completed',
            $statuses->every(fn($s) => in_array($s, ['shipped', 'completed'])) => 'shipped',
            $statuses->contains('shipped') || $statuses->contains('processing') => 'processing',
            $statuses->every(fn($s) => in_array($s, ['confirmed', 'processing', 'shipped', 'completed'])) => 'processing',
            $statuses->contains('confirmed') => 'paid',
            default => $order->status, // keep current
        };

        if ($order->status !== $orderStatus) {
            $order->update(['status' => $orderStatus]);
        }
    }

    protected function updateStoreRating(\App\Models\Store $store): void
    {
        $rating = \App\Models\Review::whereHas('product', fn($q) => $q->where('store_id', $store->id))
            ->where('status', 'visible')
            ->avg('rating');

        $count = \App\Models\Review::whereHas('product', fn($q) => $q->where('store_id', $store->id))
            ->where('status', 'visible')
            ->count();

        $store->update([
            'rating_avg' => round($rating, 2),
            'rating_count' => $count,
        ]);
    }
}
