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
        $query = SubOrder::with(['order.user', 'items.variant.product', 'store', 'shipment'])
            ->whereHas('store', fn($q) => $q->where('seller_id', $user->seller?->id))
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(10);
    }

    /**
     * Confirm a sub-order (seller action). The seller controller immediately
     * ships it afterwards, so confirmation and shipping are one step.
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

            // Restore stock. After payment success the stock was DEDUCTED from
            // quantity (MidtransService::markOrderPaid); before that it was only
            // RESERVED. Restore through the right path so stock is never lost or
            // double-counted.
            $stockWasDeducted = in_array($subOrder->order->status, ['paid', 'processing', 'shipped']);

            foreach ($subOrder->items as $item) {
                $inventory = $item->variant->inventory;

                if ($stockWasDeducted) {
                    $inventory->increment('quantity', $item->quantity);
                } else {
                    $inventory->release($item->quantity);
                }

                // Roll back the sold counter that was incremented at checkout
                $item->variant->product->decrement('sold_count', $item->quantity);
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

        // Sync parent order status (all cancelled => order cancelled)
        $this->syncOrderStatus($subOrder->order->refresh());

        return $subOrder;
    }

    /**
     * Hook fired when a sub-order reaches 'completed' (via the automatic
     * delivery simulation): updates store rating and syncs the parent order.
     */
    public function onSubOrderCompleted(SubOrder $subOrder): void
    {
        $this->updateStoreRating($subOrder->store);
        $this->syncOrderStatus($subOrder->order);
    }

    /**
     * Sync parent Order status based on all sub-orders
     */
    public function syncOrderStatus(Order $order): void
    {
        $order->load('subOrders');
        $statuses = $order->subOrders->pluck('status')->unique()->values();

        // Determine the most advanced status across all sub-orders
        $orderStatus = match(true) {
            $statuses->every(fn($s) => $s === 'cancelled') => 'cancelled',
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
            'rating_avg' => round($rating ?? 0, 2),
            'rating_count' => $count,
        ]);
    }
}
