<?php

/**
 * HTTP-level Midtrans test against the running dev server. Verifies the exact
 * API contract the React checkout/payment pages rely on.
 *
 *   php artisan tinker --execute="require 'storage/midtrans_http_test.php'"
 */

use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

define('PAZARZ_CLEANUP_MANUAL', true);
require 'storage/cleanup_fixtures.php';

$GLOBALS['mt_pass'] = 0;
$GLOBALS['mt_fail'] = 0;

function hcheck(string $label, bool $cond, string $extra = ''): void
{
    if ($cond) {
        $GLOBALS['mt_pass']++;
        echo "  PASS  $label\n";
    } else {
        $GLOBALS['mt_fail']++;
        echo "  FAIL  $label" . ($extra ? "  <$extra>" : '') . "\n";
    }
}

$base = 'http://127.0.0.1:8000/api/v1';
$suffix = substr((string) time(), -7);
$cleanup = [];

echo "\n=== Midtrans HTTP E2E (suffix {$suffix}) ===\n";

try {
    $sellerUser = User::create([
        'name' => 'HT Seller', 'email' => "ht-seller-{$suffix}@test.local", 'role_id' => 'seller',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $customer = User::create([
        'name' => 'HT Customer', 'email' => "ht-cust-{$suffix}@test.local", 'role_id' => 'customer',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $cleanup[] = fn () => DB::table('notifications')
        ->where('notifiable_type', User::class)
        ->whereIn('notifiable_id', [$sellerUser->id, $customer->id])->delete();
    $cleanup[] = fn () => DB::table('users')->whereIn('id', [$sellerUser->id, $customer->id])->delete();

    $sellerId = DB::table('sellers')->insertGetId([
        'user_id' => $sellerUser->id, 'business_name' => "HT Store {$suffix}", 'business_type' => 'individual',
        'verification_status' => 'approved', 'commission_rate' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $storeId = DB::table('stores')->insertGetId([
        'seller_id' => $sellerId, 'name' => "HT Store {$suffix}", 'slug' => "ht-store-{$suffix}",
        'status' => 'active', 'rating_avg' => 0, 'rating_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $categoryId = DB::table('categories')->insertGetId([
        'name' => "HT Cat {$suffix}", 'slug' => "ht-cat-{$suffix}", 'is_active' => 1, 'sort_order' => 0,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $productId = DB::table('products')->insertGetId([
        'store_id' => $storeId, 'category_id' => $categoryId, 'name' => "HT Product {$suffix}",
        'slug' => "ht-product-{$suffix}", 'base_price' => 15000, 'status' => 'active', 'weight_grams' => 500,
        'rating_avg' => 0, 'rating_count' => 0, 'sold_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $variantId = DB::table('product_variants')->insertGetId([
        'product_id' => $productId, 'sku' => "HT-SKU-{$suffix}", 'price' => 15000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $inventoryId = DB::table('inventories')->insertGetId([
        'product_variant_id' => $variantId, 'quantity' => 20, 'reserved_quantity' => 0,
        'low_stock_threshold' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $addressId = DB::table('addresses')->insertGetId([
        'addressable_type' => User::class, 'addressable_id' => $customer->id, 'label' => 'Home',
        'recipient_name' => 'HT Customer', 'phone' => '08123456789', 'province' => 'DKI Jakarta',
        'city' => 'Jakarta', 'district' => 'Kebayoran', 'postal_code' => '12140',
        'full_address' => 'Jl. HTTP No. 1', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    $cleanup[] = fn () => DB::table('inventories')->where('id', $inventoryId)->delete();
    $cleanup[] = fn () => DB::table('product_variants')->where('id', $variantId)->delete();
    $cleanup[] = fn () => DB::table('products')->where('id', $productId)->delete();
    $cleanup[] = fn () => DB::table('stores')->where('id', $storeId)->delete();
    $cleanup[] = fn () => DB::table('sellers')->where('id', $sellerId)->delete();
    $cleanup[] = fn () => DB::table('categories')->where('id', $categoryId)->delete();
    $cleanup[] = fn () => DB::table('addresses')->where('id', $addressId)->delete();

    $cartId = DB::table('carts')->where('user_id', $customer->id)->value('id')
        ?: DB::table('carts')->insertGetId(['user_id' => $customer->id, 'created_at' => now(), 'updated_at' => now()]);
    $cartItemId = DB::table('cart_items')->insertGetId([
        'cart_id' => $cartId, 'product_variant_id' => $variantId, 'quantity' => 1, 'price_snapshot' => 15000,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    $cleanup[] = fn () => DB::table('cart_items')->where('id', $cartItemId)->delete();

    $order = app(CheckoutService::class)
        ->checkout($customer, $addressId, [$storeId => ['courier' => 'jne', 'cost' => 0]])['order'];

    $cleanup[] = fn () => DB::table('order_items')->where('sub_order_id', $order->subOrders->first()->id)->delete();
    $cleanup[] = fn () => DB::table('sub_orders')->where('order_id', $order->id)->delete();
    $cleanup[] = fn () => DB::table('payments')->where('order_id', $order->id)->delete();
    $cleanup[] = fn () => DB::table('orders')->where('id', $order->id)->delete();

    $token = $customer->createToken('http-test')->plainTextToken;
    $auth = fn () => Http::withToken($token)->acceptJson();

    // ---------------------------------------- unauthenticated pay is rejected
    echo "\n[1] Auth is required on the pay endpoint\n";
    $anon = Http::acceptJson()->post("{$base}/orders/{$order->order_number}/pay");
    hcheck('anon pay -> 401', $anon->status() === 401, (string) $anon->status());

    // ---------------------------------------------------- POST .../pay
    echo "\n[2] POST /orders/{number}/pay returns a Snap token\n";
    $pay = $auth()->post("{$base}/orders/{$order->order_number}/pay");
    hcheck('pay -> 200', $pay->status() === 200, (string) $pay->status() . ' ' . $pay->body());
    hcheck('pay success flag', $pay->json('success') === true);
    hcheck('snap token present', ! empty($pay->json('data.token')));
    hcheck('client key present', $pay->json('data.client_key') === config('midtrans.client_key'));
    hcheck('server key absent from response', ! str_contains($pay->body(), (string) config('midtrans.server_key')));

    // ---------------------------------------------- GET payment-status
    echo "\n[3] GET /orders/{number}/payment-status reports pending\n";
    $status = $auth()->get("{$base}/orders/{$order->order_number}/payment-status");
    hcheck('status -> 200', $status->status() === 200, (string) $status->status());
    hcheck('order_status pending_payment', $status->json('data.order_status') === 'pending_payment');
    hcheck('retryable true', $status->json('data.retryable') === true);

    // ------------------------------------------------- public webhook
    echo "\n[4] POST /payment/notification settles the payment (no auth)\n";
    $gross = number_format((float) $order->grand_total, 2, '.', '');
    $payload = [
        'order_id' => $order->order_number,
        'status_code' => '200',
        'gross_amount' => $gross,
        'signature_key' => hash('sha512', $order->order_number . '200' . $gross . config('midtrans.server_key')),
        'transaction_status' => 'settlement',
        'fraud_status' => 'accept',
        'payment_type' => 'qris',
        'transaction_id' => 'http-txn-' . $suffix,
    ];

    $notify = Http::acceptJson()->post("{$base}/payment/notification", $payload);
    hcheck('notification -> 200', $notify->status() === 200, (string) $notify->status() . ' ' . $notify->body());
    hcheck('notification applied', $notify->json('data.applied.changed') === true);

    $dup = Http::acceptJson()->post("{$base}/payment/notification", $payload);
    hcheck('duplicate notification -> 200', $dup->status() === 200);
    hcheck('duplicate not re-applied', $dup->json('data.applied.changed') === false);

    $bad = Http::acceptJson()->post("{$base}/payment/notification", array_merge($payload, ['signature_key' => str_repeat('a', 128)]));
    hcheck('bad signature -> 403', $bad->status() === 403, (string) $bad->status());

    echo "\n[5] payment-status now reports paid\n";
    $status2 = $auth()->get("{$base}/orders/{$order->order_number}/payment-status");
    hcheck('order_status paid', $status2->json('data.order_status') === 'paid', (string) $status2->json('data.order_status'));
    hcheck('payment_status success', $status2->json('data.payment_status') === 'success');
    hcheck('retryable false', $status2->json('data.retryable') === false);

    echo "\n[6] Order detail API reflects paid state (frontend contract)\n";
    $detail = $auth()->get("{$base}/orders/{$order->order_number}");
    hcheck('detail -> 200', $detail->status() === 200, (string) $detail->status());
    hcheck('detail status paid', $detail->json('data.status') === 'paid');
    hcheck('detail payment method set', ! empty($detail->json('data.payment.method')));
    hcheck('no payment_instructions leaked', ! str_contains($detail->body(), 'payment_instructions'));

    hcheck('server key absent from all responses', ! str_contains($pay->body() . $status2->body() . $detail->body(), (string) config('midtrans.server_key')));
} catch (\Throwable $e) {
    echo "\n!! ABORTED: " . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine() . "\n";
    $GLOBALS['mt_fail']++;
} finally {
    pazarz_cleanup_report(['ht-']);
    echo "\n=== RESULT: {$GLOBALS['mt_pass']} passed, {$GLOBALS['mt_fail']} failed ===\n";
}
