<?php

/**
 * End-to-end Midtrans Sandbox test. Run with:
 *   php artisan tinker --execute="require 'storage/midtrans_e2e.php'"
 *
 * It exercises the REAL Midtrans Sandbox API for token creation and the
 * signature-verified webhook path, then cleans up after itself.
 */

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

define('PAZARZ_CLEANUP_MANUAL', true);
require 'storage/cleanup_fixtures.php';

$GLOBALS['mt_pass'] = 0;
$GLOBALS['mt_fail'] = 0;
$suffix = substr((string) time(), -7);

function check(string $label, bool $cond, string $extra = ''): void
{
    if ($cond) {
        $GLOBALS['mt_pass']++;
        echo "  PASS  $label\n";
    } else {
        $GLOBALS['mt_fail']++;
        echo "  FAIL  $label" . ($extra ? "  <$extra>" : '') . "\n";
    }
}

$cleanup = [];

echo "\n=== Midtrans Sandbox E2E (suffix {$suffix}) ===\n";

try {
    // ---------------------------------------------------------------- fixtures
    $sellerUser = User::create([
        'name' => 'MT Seller', 'email' => "mt-seller-{$suffix}@test.local", 'role_id' => 'seller',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $customer = User::create([
        'name' => 'MT Customer', 'email' => "mt-cust-{$suffix}@test.local", 'role_id' => 'customer',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $cleanup[] = fn () => DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->whereIn('notifiable_id', [$sellerUser->id, $customer->id])
        ->delete();
    $cleanup[] = fn () => DB::table('users')->whereIn('id', [$sellerUser->id, $customer->id])->delete();

    $sellerId = DB::table('sellers')->insertGetId([
        'user_id' => $sellerUser->id, 'business_name' => "MT Store {$suffix}", 'business_type' => 'individual',
        'verification_status' => 'approved', 'commission_rate' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $storeId = DB::table('stores')->insertGetId([
        'seller_id' => $sellerId, 'name' => "MT Store {$suffix}", 'slug' => "mt-store-{$suffix}",
        'status' => 'active', 'rating_avg' => 0, 'rating_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $categoryId = DB::table('categories')->insertGetId([
        'name' => "MT Cat {$suffix}", 'slug' => "mt-cat-{$suffix}", 'is_active' => 1, 'sort_order' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $productId = DB::table('products')->insertGetId([
        'store_id' => $storeId, 'category_id' => $categoryId, 'name' => "MT Product {$suffix}",
        'slug' => "mt-product-{$suffix}", 'base_price' => 12000, 'status' => 'active', 'weight_grams' => 500,
        'rating_avg' => 0, 'rating_count' => 0, 'sold_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $variantId = DB::table('product_variants')->insertGetId([
        'product_id' => $productId, 'sku' => "MT-SKU-{$suffix}", 'price' => 12000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $inventoryId = DB::table('inventories')->insertGetId([
        'product_variant_id' => $variantId, 'quantity' => 50, 'reserved_quantity' => 0,
        'low_stock_threshold' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $cleanup[] = fn () => DB::table('inventories')->where('id', $inventoryId)->delete();
    $cleanup[] = fn () => DB::table('product_variants')->where('id', $variantId)->delete();
    $cleanup[] = fn () => DB::table('products')->where('id', $productId)->delete();
    $cleanup[] = fn () => DB::table('stores')->where('id', $storeId)->delete();
    $cleanup[] = fn () => DB::table('sellers')->where('id', $sellerId)->delete();
    $cleanup[] = fn () => DB::table('categories')->where('id', $categoryId)->delete();

    $addressId = DB::table('addresses')->insertGetId([
        'addressable_type' => User::class, 'addressable_id' => $customer->id, 'label' => 'Home',
        'recipient_name' => 'MT Customer', 'phone' => '08123456789', 'province' => 'DKI Jakarta',
        'city' => 'Jakarta', 'district' => 'Kebayoran', 'postal_code' => '12140',
        'full_address' => 'Jl. Test No. 1', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cleanup[] = fn () => DB::table('addresses')->where('id', $addressId)->delete();

    // Cart with one item. Pazarz auto-creates a cart per user (User::booted),
    // so reuse whichever cart already belongs to this customer.
    $cartId = DB::table('carts')->where('user_id', $customer->id)->value('id');
    if (! $cartId) {
        $cartId = DB::table('carts')->insertGetId([
            'user_id' => $customer->id, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
    $cartItemId = DB::table('cart_items')->insertGetId([
        'cart_id' => $cartId, 'product_variant_id' => $variantId, 'quantity' => 2, 'price_snapshot' => 12000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $cleanup[] = fn () => DB::table('cart_items')->where('id', $cartItemId)->delete();
    $cleanup[] = fn () => DB::table('carts')->where('id', $cartId)->delete();

    $stock = fn () => (int) DB::table('inventories')->where('id', $inventoryId)->value('quantity');
    $reserved = fn () => (int) DB::table('inventories')->where('id', $inventoryId)->value('reserved_quantity');
    $paymentOf = fn (Order $o) => Payment::where('order_id', $o->id)->first();

    // ------------------------------------------------------------- 1. checkout
    echo "\n[1] Checkout creates order + pending Midtrans payment\n";
    $result = app(CheckoutService::class)->checkout($customer, $addressId, [
        $storeId => ['courier' => 'jne', 'cost' => 0],
    ]);

    $orderA = $result['order'];
    $cleanup[] = fn () => DB::table('order_items')->where('sub_order_id', $orderA->subOrders->first()->id)->delete();
    $cleanup[] = fn () => DB::table('sub_orders')->where('order_id', $orderA->id)->delete();
    $cleanup[] = fn () => DB::table('payments')->where('order_id', $orderA->id)->delete();
    $cleanup[] = fn () => DB::table('orders')->where('id', $orderA->id)->delete();

    check('order status is pending_payment', $orderA->status === 'pending_payment', $orderA->status);
    check('grand_total = 24000', (float) $orderA->grand_total === 24000.0, (string) $orderA->grand_total);
    check('payment provider = midtrans', $paymentOf($orderA)?->provider === 'midtrans');
    check('payment status = pending', $paymentOf($orderA)?->status === 'pending');
    check('stock reserved by 2', $reserved() === 2, 'reserved=' . $reserved());
    check('stock not yet deducted', $stock() === 50, 'qty=' . $stock());
    check('no manual payment instructions in response', ! array_key_exists('payment_instructions', $result));

    // ------------------------------------------------- 2. real Snap token
    echo "\n[2] Real Midtrans Sandbox Snap token\n";
    $midtrans = app(MidtransService::class);
    $snap = null;
    try {
        $snap = $midtrans->createSnapTransaction($orderA->fresh());
    } catch (\Throwable $e) {
        check('createSnapTransaction succeeded', false, $e->getMessage());
    }

    if ($snap) {
        check('snap token returned', ! empty($snap['token']));
        check('client key exposed (public)', ($snap['client_key'] ?? null) === config('midtrans.client_key'));
        check('server key NOT in payload', ! str_contains(json_encode($snap), (string) config('midtrans.server_key')));
        check('snap uses sandbox url', str_contains($snap['snap_js_url'] ?? '', 'sandbox'));
        check('redirect_url returned', ! empty($snap['redirect_url']));
    } else {
        // Do not continue silently if Midtrans is unreachable.
        throw new \Exception('Snap token was not created — cannot continue the payment test.');
    }

    // Calling pay again must reuse the same Payment row (no duplicates).
    $midtrans->createSnapTransaction($orderA->fresh());
    check('retry does not create a second Payment row', Payment::where('order_id', $orderA->id)->count() === 1);

    // ------------------------------------------ 3. signed webhook -> paid
    echo "\n[3] Signed settlement notification marks Payment + Order paid\n";
    $gross = number_format((float) $orderA->grand_total, 2, '.', '');
    $statusCode = '200';
    $signature = hash('sha512', $orderA->order_number . $statusCode . $gross . config('midtrans.server_key'));

    $notification = [
        'order_id' => $orderA->order_number,
        'status_code' => $statusCode,
        'gross_amount' => $gross,
        'signature_key' => $signature,
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'bank_transfer',
        'transaction_id' => 'txn-' . $suffix,
    ];

    $out = $midtrans->handleNotification($notification);
    $orderA->refresh();
    check('notification processed', ($out['applied']['changed'] ?? false) === true);
    check('payment status = success', $paymentOf($orderA)?->status === 'success', (string) $paymentOf($orderA)?->status);
    check('order status = paid', $orderA->status === 'paid', $orderA->status);
    check('stock deducted exactly once (50 -> 48)', $stock() === 48, 'qty=' . $stock());
    check('reserved cleared', $reserved() === 0, 'reserved=' . $reserved());
    check('paid_at set', $paymentOf($orderA)?->paid_at !== null);
    check('payment method reflects midtrans type', str_contains((string) $paymentOf($orderA)?->method, 'bank_transfer'));
    check('customer notified', DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->where('notifiable_id', $customer->id)
        ->where('type', 'payment_success')
        ->exists());

    // ------------------------------------------------- 4. duplicate webhook
    echo "\n[4] Duplicate notification is idempotent\n";
    $before = $stock();
    $out2 = $midtrans->handleNotification($notification);
    check('duplicate reports no change', ($out2['applied']['changed'] ?? false) === false);
    check('stock unchanged after duplicate', $stock() === $before, 'qty=' . $stock());
    check('still exactly one payment row', Payment::where('order_id', $orderA->id)->count() === 1);

    // Replay a "capture" after settlement must also be a no-op.
    $midtrans->handleNotification(array_merge($notification, ['transaction_status' => 'capture']));
    check('late capture after settlement is a no-op', $stock() === $before);

    // --------------------------------------------------- 5. bad signature
    echo "\n[5] Invalid signature is rejected\n";
    $rejected = false;
    try {
        $midtrans->handleNotification(array_merge($notification, ['signature_key' => str_repeat('0', 128)]));
    } catch (\Throwable $e) {
        $rejected = true;
    }
    check('tampered notification rejected', $rejected);
    check('tampered notification did not change stock', $stock() === $before);

    // --------------------------------- 6. expired payment stays retryable
    echo "\n[6] Expired payment stays retryable (order keeps awaiting payment)\n";
    DB::table('cart_items')->insert([
        'cart_id' => $cartId, 'product_variant_id' => $variantId, 'quantity' => 1, 'price_snapshot' => 12000,
        'created_at' => now(), 'updated_at' => now(),
    ]);

    $orderB = app(CheckoutService::class)->checkout($customer, $addressId, [
        $storeId => ['courier' => 'jne', 'cost' => 0],
    ])['order'];

    $cleanup[] = fn () => DB::table('order_items')->where('sub_order_id', $orderB->subOrders->first()->id)->delete();
    $cleanup[] = fn () => DB::table('sub_orders')->where('order_id', $orderB->id)->delete();
    $cleanup[] = fn () => DB::table('payments')->where('order_id', $orderB->id)->delete();
    $cleanup[] = fn () => DB::table('orders')->where('id', $orderB->id)->delete();

    $midtrans->createSnapTransaction($orderB->fresh());

    $grossB = number_format((float) $orderB->grand_total, 2, '.', '');
    $sigB = hash('sha512', $orderB->order_number . '200' . $grossB . config('midtrans.server_key'));

    $midtrans->handleNotification([
        'order_id' => $orderB->order_number,
        'status_code' => '200',
        'gross_amount' => $grossB,
        'signature_key' => $sigB,
        'transaction_status' => 'expire',
        'transaction_id' => 'txn-exp-' . $suffix,
    ]);

    $orderB->refresh();
    check('payment status = expired', $paymentOf($orderB)?->status === 'expired', (string) $paymentOf($orderB)?->status);
    check('order still awaits payment', $orderB->status === 'pending_payment', $orderB->status);
    check('stock stays reserved for a retry', $reserved() === 1, 'reserved=' . $reserved());
    check('stock not deducted on expiry', $stock() === 48, 'qty=' . $stock());

    // Retry: a fresh Snap token for the same order_id must still be possible.
    $retry = null;
    try {
        $retry = $midtrans->createSnapTransaction($orderB->fresh());
    } catch (\Throwable $e) {
        check('retry after expiry succeeds', false, $e->getMessage());
    }
    check('retry after expiry returns a fresh token', ! empty($retry['token'] ?? null));
    check('expired payment reset to pending', $paymentOf($orderB)?->status === 'pending', (string) $paymentOf($orderB)?->status);
    check('still a single Payment row after retry', Payment::where('order_id', $orderB->id)->count() === 1);

    // --------------------------------- 7. hard failure cancels + releases
    echo "\n[7] Denied payment cancels the order and releases stock\n";
    $grossB2 = number_format((float) $orderB->grand_total, 2, '.', '');
    $sigB2 = hash('sha512', $orderB->order_number . '200' . $grossB2 . config('midtrans.server_key'));

    $midtrans->handleNotification([
        'order_id' => $orderB->order_number,
        'status_code' => '200',
        'gross_amount' => $grossB2,
        'signature_key' => $sigB2,
        'transaction_status' => 'deny',
        'transaction_id' => 'txn-deny-' . $suffix,
    ]);

    $orderB->refresh();
    check('payment status = failed', $paymentOf($orderB)?->status === 'failed', (string) $paymentOf($orderB)?->status);
    check('order cancelled on failure', $orderB->status === 'cancelled', $orderB->status);
    check('reserved stock released', $reserved() === 0, 'reserved=' . $reserved());
    check('stock still 48 (failure did not deduct)', $stock() === 48, 'qty=' . $stock());

    // ------------------------------------ 8. pay is blocked once settled
    echo "\n[8] Paying a settled order is rejected\n";
    $blocked = false;
    try {
        $midtrans->createSnapTransaction($orderA->fresh());
    } catch (\Throwable $e) {
        $blocked = true;
    }
    check('pay blocked for non-pending order', $blocked);
} catch (\Throwable $e) {
    echo "\n!! ABORTED: " . $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    $GLOBALS['mt_fail']++;
} finally {
    pazarz_cleanup_report(['mt-']);
    echo "\n=== RESULT: {$GLOBALS['mt_pass']} passed, {$GLOBALS['mt_fail']} failed ===\n";
}
