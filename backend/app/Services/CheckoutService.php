<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SubOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Process checkout from cart
     */
    public function checkout(
        User $user,
        int $shippingAddressId,
        array $shippingMethodsPerStore, // [store_id => ['courier' => 'jne', 'cost' => 15000]]
        ?string $couponCode = null
    ): array {
        return DB::transaction(function () use ($user, $shippingAddressId, $shippingMethodsPerStore, $couponCode) {
            $cart = Cart::with(['items.variant.product.store', 'items.variant.inventory'])->where('user_id', $user->id)->firstOrFail();

            if ($cart->items->isEmpty()) {
                throw new \Exception('Cart is empty.');
            }

            // Validate shipping address
            $shippingAddress = Address::where('addressable_type', User::class)
                ->where('addressable_id', $user->id)
                ->where('id', $shippingAddressId)
                ->firstOrFail();

            // Validate stock for all items
            foreach ($cart->items as $item) {
                if (!$item->variant->inventory || !$item->variant->inventory->hasStock($item->quantity)) {
                    throw new \App\Exceptions\InsufficientStockException(
                        "Insufficient stock for {$item->variant->product->name} ({$item->variant->sku})"
                    );
                }
            }

            // Group cart items by store
            $groupedItems = $cart->items->groupBy(fn($item) => $item->variant->product->store_id);

            // Calculate totals
            $subtotal = 0;
            $shippingTotal = 0;
            $discountTotal = 0;

            // Apply coupon if provided
            $coupon = null;
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();
                if (!$coupon || !$coupon->isUsableBy($user, $cart->subtotal)) {
                    throw new \Exception('Invalid or expired coupon code.');
                }
                $discountTotal = $coupon->calculateDiscount($cart->subtotal);
            }

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'shipping_address_id' => $shippingAddressId,
                'subtotal' => $cart->subtotal,
                'shipping_total' => 0, // Will be calculated per sub-order
                'discount_total' => $discountTotal,
                'grand_total' => 0, // Will be calculated after shipping
                'status' => 'pending_payment',
                'placed_at' => now(),
            ]);

            // Create sub-orders per store
            foreach ($groupedItems as $storeId => $items) {
                $storeSubtotal = $items->sum(fn($item) => $item->price_snapshot * $item->quantity);

                // Get shipping method/cost for this store
                $shippingMethod = $shippingMethodsPerStore[$storeId] ?? null;
                $shippingCost = $shippingMethod['cost'] ?? 0;

                $subOrder = SubOrder::create([
                    'order_id' => $order->id,
                    'store_id' => $storeId,
                    'subtotal' => $storeSubtotal,
                    'shipping_cost' => $shippingCost,
                    'status' => 'pending',
                ]);

                // Create order items with snapshots
                foreach ($items as $item) {
                    $variant = $item->variant;
                    $product = $variant->product;

                    OrderItem::create([
                        'sub_order_id' => $subOrder->id,
                        'product_variant_id' => $variant->id,
                        'product_name_snapshot' => $product->name,
                        'variant_label_snapshot' => $variant->label,
                        'price_snapshot' => $item->price_snapshot,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->price_snapshot * $item->quantity,
                    ]);

                    // Reserve stock
                    $variant->inventory->reserve($item->quantity);

                    // Update product sold count
                    $product->increment('sold_count', $item->quantity);
                }

                $shippingTotal += $shippingCost;
            }

            // Update order totals
            $grandTotal = $order->subtotal + $shippingTotal - $discountTotal;
            $order->update([
                'shipping_total' => $shippingTotal,
                'grand_total' => $grandTotal,
            ]);

            // Record coupon usage
            if ($coupon) {
                $coupon->usages()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
            }

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'method' => 'va', // Default, will be updated by payment gateway
                'amount' => $grandTotal,
                'status' => 'pending',
            ]);

            // Clear cart
            $cart->items()->delete();

            // Create notifications
            foreach ($groupedItems as $storeId => $items) {
                $store = $items->first()->variant->product->store;
                if ($store && $store->seller && $store->seller->user) {
                    \App\Models\Notification::createForUser(
                        $store->seller->user,
                        'new_order',
                        'New Order Received',
                        "You have a new order #{$order->order_number}",
                        ['order_id' => $order->id, 'order_number' => $order->order_number]
                    );
                }
            }

            return [
                'order' => $order->load(['subOrders.store', 'subOrders.items.variant', 'payment', 'shippingAddress']),
                'payment_instructions' => [
                    'method' => 'va',
                    'amount' => $grandTotal,
                    'virtual_account_number' => 'VA' . strtoupper(uniqid()),
                    'bank' => 'BCA',
                    'expires_at' => now()->addHours(24)->toIso8601String(),
                ],
            ];
        });
    }

    /**
     * Handle payment callback from payment gateway
     */
    public function handlePaymentCallback(string $providerReference, string $status, array $metadata = []): void
    {
        DB::transaction(function () use ($providerReference, $status, $metadata) {
            $payment = Payment::where('provider_reference', $providerReference)->firstOrFail();

            if ($payment->status !== 'pending') {
                return; // Idempotent - already processed
            }

            $payment->update([
                'status' => $status,
                'provider' => $metadata['provider'] ?? null,
                'paid_at' => $status === 'success' ? now() : null,
            ]);

            if ($status === 'success') {
                $payment->order->update(['status' => 'paid']);

                // Deduct stock from reserved
                foreach ($payment->order->subOrders as $subOrder) {
                    foreach ($subOrder->items as $item) {
                        $item->variant->inventory->deduct($item->quantity);
                    }
                }

                // Notify customer
                \App\Models\Notification::createForUser(
                    $payment->order->user,
                    'payment_success',
                    'Payment Successful',
                    "Your order #{$payment->order->order_number} has been paid.",
                    ['order_id' => $payment->order->id]
                );
            } elseif ($status === 'failed') {
                $payment->order->update(['status' => 'cancelled']);

                // Release reserved stock
                foreach ($payment->order->subOrders as $subOrder) {
                    foreach ($subOrder->items as $item) {
                        $item->variant->inventory->release($item->quantity);
                    }
                }
            }
        });
    }
}
