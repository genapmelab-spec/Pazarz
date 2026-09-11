<?php

/**
 * Focused checks: (a) real server-to-server Midtrans status sync, and
 * (b) the seller confirm/ship gate requires a paid order.
 *
 *   php artisan tinker --execute="require 'storage/midtrans_extra_test.php'"
 */

use App\Http\Controllers\Web\Seller\OrderController;
use App\Models\User;
use App\Services\CheckoutService;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

define('PAZARZ_CLEANUP_MANUAL', true);
require 'storage/cleanup_fixtures.php';

$GLOBALS['mt_pass'] = 0;
$GLOBALS['mt_fail'] = 0;

function xcheck(string $label, bool $cond, string $extra = ''): void
{
    if ($cond) {
        $GLOBALS['mt_pass']++;
        echo "  PASS  $label\n";
    } else {
        $GLOBALS['mt_fail']++;
        echo "  FAIL  $label" . ($extra ? "  <$extra>" : '') . "\n";
    }
}

$suffix = substr((string) time(), -7);
$cleanup = [];

echo "\n=== Midtrans sync + seller gate (suffix {$suffix}) ===\n";

try {
    $sellerUser = User::create([
        'name' => 'SG Seller', 'email' => "sg-seller-{$suffix}@test.local", 'role_id' => 'seller',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $customer = User::create([
        'name' => 'SG Customer', 'email' => "sg-cust-{$suffix}@test.local", 'role_id' => 'customer',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $cleanup[] = fn () => DB::table('notifications')->where('notifiable_type', User::class)
        ->whereIn('notifiable_id', [$sellerUser->id, $customer->id])->delete();
    $cleanup[] = fn () => DB::table('users')->whereIn('id', [$sellerUser->id, $customer->id])->delete();
    $cleanup[] = fn () => DB::table('addresses')->where('addressable_type', User::class)
        ->whereIn('addressable_id', [$sellerUser->id, $customer->id])->delete();

    $sellerId = DB::table('sellers')->insertGetId([
        'user_id' => $sellerUser->id, 'business_name' => "SG Store {$suffix}", 'business_type' => 'individual',
        'verification_status' => 'approved', 'commission_rate' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $storeId = DB::table('stores')->insertGetId([
        'seller_id' => $sellerId, 'name' => "SG Store {$suffix}", 'slug' => "sg-store-{$suffix}",
        'status' => 'active', 'rating_avg' => 0, 'rating_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $categoryId = DB::table('categories')->insertGetId([
        'name' => "SG Cat {$suffix}", 'slug' => "sg-cat-{$suffix}", 'is_active' => 1, 'sort_order' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $productId = DB::table('products')->insertGetId([
        'store_id' => $storeId, 'category_id' => $categoryId, 'name' => "SG Product {$suffix}",
        'slug' => "sg-product-{$suffix}", 'base_price' => 9000, 'status' => 'active', 'weight_grams' => 300,
        'rating_avg' => 0, 'rating_count' => 0, 'sold_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $variantId = DB::table('product_variants')->insertGetId([
        'product_id' => $productId, 'sku' => "SG-SKU-{$suffix}", 'price' => 9000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $inventoryId = DB::table('inventories')->insertGetId([
        'product_variant_id' => $variantId, 'quantity' => 10, 'reserved_quantity' => 0,
        'low_stock_threshold' => 2, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $addressId = DB::table('addresses')->insertGetId([
        'addressable_type' => User::class, 'addressable_id' => $customer->id, 'label' => 'Home',
        'recipient_name' => 'SG Customer', 'phone' => '0812', 'province' => 'DKI Jakarta',
        'city' => 'Jakarta', 'district' => 'Kebayoran', 'postal_code' => '12140',
        'full_address' => 'Jl. Gate No. 1', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $cleanup[] = fn () => DB::table('shipment_tracking_events')->whereIn('shipment_id',
        DB::table('shipments')->whereIn('sub_order_id', DB::table('sub_orders')->where('store_id', $storeId)->pluck('id'))->pluck('id'))->delete();
    $cleanup[] = fn () => DB::table('shipments')->whereIn('sub_order_id',
        DB::table('sub_orders')->where('store_id', $storeId)->pluck('id'))->delete();
    $cleanup[] = fn () => DB::table('sub_orders')->where('store_id', $storeId)->delete();
    $cleanup[] = fn () => DB::table('orders')->where('user_id', $customer->id)->delete();
    $cleanup[] = fn () => DB::table('payments')->whereIn('order_id',
        DB::table('orders')->where('user_id', $customer->id)->pluck('id'))->delete();
    $cleanup[] = fn () => DB::table('inventories')->where('id', $inventoryId)->delete();
    $cleanup[] = fn () => DB::table('product_variants')->where('id', $variantId)->delete();
    $cleanup[] = fn () => DB::table('products')->where('id', $productId)->delete();
    $cleanup[] = fn () => DB::table('stores')->where('id', $storeId)->delete();
    $cleanup[] = fn () => DB::table('sellers')->where('id', $sellerId)->delete();
    $cleanup[] = fn () => DB::table('categories')->where('id', $categoryId)->delete();

    $cartId = DB::table('carts')->where('user_id', $customer->id)->value('id');
    $itemId = DB::table('cart_items')->insertGetId([
        'cart_id' => $cartId, 'product_variant_id' => $variantId, 'quantity' => 1, 'price_snapshot' => 9000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $cleanup[] = fn () => DB::table('cart_items')->where('id', $itemId)->delete();

    $order = app(CheckoutService::class)
        ->checkout($customer, $addressId, [$storeId => ['courier' => 'jne', 'cost' => 0]])['order'];

    $midtrans = app(MidtransService::class);
    $midtrans->createSnapTransaction($order->fresh());

    // -------------------------------------- (a) real Midtrans status lookup
    echo "\n[a] Server-to-server Midtrans status sync\n";
    $resp = Http::withBasicAuth(config('midtrans.server_key'), '')
        ->acceptJson()
        ->get(config('midtrans.api_base_url') . "/v2/{$order->order_number}/status");

    // Midtrans answers HTTP 200 even for "Transaction doesn't exist" (it puts
    // 404 in status_code), so both shapes must be handled without error.
    xcheck('Midtrans status endpoint reachable', $resp->status() === 200, 'http ' . $resp->status());
    xcheck(
        'Midtrans status response handled (transaction or not-created-yet)',
        ! empty($resp->json('transaction_status')) || $resp->json('status_code') === '404',
        'body=' . $resp->body()
    );
    xcheck('a not-yet-existing transaction never marks the order paid', $order->fresh()->status === 'pending_payment');

    $synced = $midtrans->syncOrderStatus($order->fresh());
    xcheck('syncOrderStatus returns a Payment', $synced !== null);
    xcheck('sync keeps payment pending (nothing paid yet)', $synced?->status === 'pending', (string) $synced?->status);
    xcheck('sync keeps order pending_payment', $order->fresh()->status === 'pending_payment', $order->fresh()->status);

    // ---------------------------------------------- (b) seller pay-gate
    echo "\n[b] Seller confirm/ship requires payment\n";
    $sellerUser->assignRole('seller');
    Auth::login($sellerUser->fresh());

    $controller = app(OrderController::class);
    $subOrder = $order->subOrders()->first();
    xcheck('sub-order starts pending', $subOrder->status === 'pending', $subOrder->status);

    $response = $controller->confirm($subOrder);
    $subOrder->refresh();
    xcheck('unpaid confirm redirected with error', session('error') !== null, (string) session('error'));
    xcheck('unpaid confirm did not ship', $subOrder->status === 'pending', $subOrder->status);
    xcheck('unpaid confirm created no shipment', $subOrder->shipment === null);

    // Now the customer pays.
    $order->update(['status' => 'paid']);
    $response2 = $controller->confirm($order->subOrders()->first());
    $subOrder->refresh();
    xcheck('paid confirm ships the sub-order', $subOrder->status === 'shipped', $subOrder->status);
    xcheck('paid confirm created a shipment', $subOrder->shipment !== null);
    xcheck('shipment auto-started for delivery', $subOrder->shipment?->simulation_state === 'running');
    xcheck('shipment status in_transit', $subOrder->shipment?->status === 'in_transit');
} catch (\Throwable $e) {
    echo "\n!! ABORTED: " . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n";
    $GLOBALS['mt_fail']++;
} finally {
    Auth::logout();
    pazarz_cleanup_report(['sg-']);
    echo "\n=== RESULT: {$GLOBALS['mt_pass']} passed, {$GLOBALS['mt_fail']} failed ===\n";
}
