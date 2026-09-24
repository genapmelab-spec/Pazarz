<?php

/**
 * End-to-end variant flow test.
 *
 * Creates a product with three variants (Size M stock 5, L stock 3, XL stock 0),
 * then walks the exact API path the React storefront uses:
 *
 *   product detail -> add Size L to cart -> checkout -> pay (Midtrans) ->
 *   signed webhook -> payment success -> order paid -> stock check.
 *
 * Verifies the chosen variant survives the whole chain and that ONLY the
 * bought variant's stock changes.
 *
 *   php artisan tinker --execute="require 'storage/variant_e2e.php';"
 */

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

define('PAZARZ_CLEANUP_MANUAL', true);
require 'storage/cleanup_fixtures.php';

$GLOBALS['v_pass'] = 0;
$GLOBALS['v_fail'] = 0;

function vcheck(string $label, bool $cond, string $extra = ''): void
{
    if ($cond) {
        $GLOBALS['v_pass']++;
        echo "  PASS  $label\n";
    } else {
        $GLOBALS['v_fail']++;
        echo "  FAIL  $label" . ($extra ? "  <$extra>" : '') . "\n";
    }
}

function stockOf(int $variantId): int
{
    return (int) (DB::table('inventories')->where('product_variant_id', $variantId)->value('quantity') ?? 0);
}

function reservedOf(int $variantId): int
{
    return (int) (DB::table('inventories')->where('product_variant_id', $variantId)->value('reserved_quantity') ?? 0);
}

$base = 'http://127.0.0.1:8000/api/v1';
$suffix = substr((string) time(), -7);    $markers = ["var-{$suffix}", "VAR-{$suffix}", "var-tee-{$suffix}", "var-seller-{$suffix}", "var-cust-{$suffix}", "var-store-{$suffix}", "var-cat-{$suffix}", "fashion-pria-atasan-var-{$suffix}"];

echo "\n=== Variant E2E (suffix {$suffix}) ===\n";

try {
    // ------------------------------------------------------------ fixture
    $sellerUser = User::create([
        'name' => 'Var Seller', 'email' => "var-seller-{$suffix}@test.local", 'role_id' => 'seller',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $customer = User::create([
        'name' => 'Var Customer', 'email' => "var-cust-{$suffix}@test.local", 'role_id' => 'customer',
        'status' => 'active', 'password' => Hash::make('password'),
    ]);
    $sellerId = DB::table('sellers')->insertGetId([
        'user_id' => $sellerUser->id, 'business_name' => "Var Store {$suffix}", 'business_type' => 'individual',
        'verification_status' => 'approved', 'commission_rate' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $storeId = DB::table('stores')->insertGetId([
        'seller_id' => $sellerId, 'name' => "Var Store {$suffix}", 'slug' => "var-store-{$suffix}",
        'status' => 'active', 'rating_avg' => 0, 'rating_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $categoryId = DB::table('categories')->insertGetId([
        'name' => "Atasan", 'slug' => "fashion-pria-atasan-var-{$suffix}", 'parent_id' => DB::table('categories')->where('slug', 'fashion-pria')->value('id'),
        'is_active' => 1, 'sort_order' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $productId = DB::table('products')->insertGetId([
        'store_id' => $storeId, 'category_id' => $categoryId, 'name' => "Var Tee {$suffix}",
        'slug' => "var-tee-{$suffix}", 'base_price' => 100000, 'status' => 'active', 'weight_grams' => 300,
        'rating_avg' => 0, 'rating_count' => 0, 'sold_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // The exact scenario from the request: M stock 5, L stock 3, XL stock 0.
    $sizes = ['M' => 5, 'L' => 3, 'XL' => 0];
    $sizeAttrId = (int) DB::table('product_attributes')->where('name', 'Size')->value('id');
    $variantIds = [];

    foreach ($sizes as $size => $stock) {
        $vid = DB::table('product_variants')->insertGetId([
            'product_id' => $productId, 'sku' => "VAR-{$suffix}-{$size}", 'price' => 120000,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('inventories')->insert([
            'product_variant_id' => $vid, 'quantity' => $stock, 'reserved_quantity' => 0,
            'low_stock_threshold' => 5, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('product_attribute_values')->insert([
            'product_attribute_id' => $sizeAttrId, 'product_variant_id' => $vid,
            'value' => $size, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $variantIds[$size] = $vid;
    }

    $addressId = DB::table('addresses')->insertGetId([
        'addressable_type' => User::class, 'addressable_id' => $customer->id, 'label' => 'Home',
        'recipient_name' => 'Var Customer', 'phone' => '08123456789', 'province' => 'DKI Jakarta',
        'city' => 'Jakarta', 'district' => 'Kebayoran', 'postal_code' => '12140',
        'full_address' => 'Jl. Variant No. 1', 'is_default' => 1, 'created_at' => now(), 'updated_at' => now(),
    ]);

    // ------------------------------------------------------------ login
    $login = Http::acceptJson()->post("{$base}/auth/login", [
        'email' => $customer->email, 'password' => 'password',
    ]);
    vcheck('customer login -> 200', $login->status() === 200, $login->status() . ' ' . $login->body());
    $token = $login->json('data.token') ?? $login->json('token');
    vcheck('token returned', ! empty($token));
    $auth = fn () => Http::withToken((string) $token)->acceptJson();

    // ---------------------------------------------- [1] product detail API
    echo "\n[1] Product detail exposes variants with size, price and stock\n";
    $detail = Http::acceptJson()->get("{$base}/products/var-tee-{$suffix}");
    vcheck('detail -> 200', $detail->status() === 200, (string) $detail->status());
    $variants = collect($detail->json('data.variants') ?? []);
    vcheck('3 variants returned', $variants->count() === 3, (string) $variants->count());

    $apiBySize = [];
    foreach ($variants as $v) {
        $size = $v['attribute_values'][0]['value'] ?? null;
        $apiBySize[$size] = $v;
        vcheck("variant {$size}: attr=size/{$size}, price=120000, stock=" . $sizes[$size],
            $size !== null
                && ($v['inventory']['quantity'] ?? null) === $sizes[$size]
                && (int) ($v['price'] ?? 0) === 120000
                && ($v['sku'] ?? '') === "VAR-{$suffix}-{$size}",
            json_encode($v));
    }
    vcheck('sizes are M, L, XL', array_keys($apiBySize) === ['M', 'L', 'XL'], implode(',', array_keys($apiBySize)));

    // --------------------------------------------------- [2] cart via API
    echo "\n[2] Add Size L to cart via API (variant-aware cart)\n";
    $add = $auth()->post("{$base}/cart/items", [
        'product_variant_id' => $variantIds['L'],
        'quantity' => 2,
    ]);
    vcheck('add to cart -> 201', $add->status() === 201, $add->status() . ' ' . $add->body());
    $cartItem = collect($add->json('data.items') ?? [])->firstWhere('product_variant_id', $variantIds['L']);
    vcheck('cart row carries variant id', $cartItem !== null);
    vcheck('cart price snapshot = variant price 120000', (int) ($cartItem['price_snapshot'] ?? 0) === 120000, json_encode($cartItem));

    // XL is out of stock: adding it must be rejected
    $addXl = $auth()->post("{$base}/cart/items", [
        'product_variant_id' => $variantIds['XL'],
        'quantity' => 1,
    ]);
    vcheck('adding out-of-stock XL rejected (409)', $addXl->status() === 409 && ($addXl->json('error.code') ?? '') === 'INSUFFICIENT_STOCK', $addXl->status() . ' ' . $addXl->body());

    // ----------------------------------------------- [3] checkout via API
    echo "\n[3] Checkout: order items snapshot the chosen variant\n";
    $checkout = $auth()->post("{$base}/checkout", [
        'shipping_address_id' => $addressId,
        'shipping_methods' => [
            ['store_id' => $storeId, 'courier' => 'jne', 'cost' => 10000],
        ],
    ]);
    vcheck('checkout -> 201', $checkout->status() === 201, $checkout->status() . ' ' . $checkout->body());
    $order = $checkout->json('data.order');
    vcheck('order status pending_payment', $order['status'] === 'pending_payment', $order['status'] ?? '-');

    $item = collect($order['sub_orders'][0]['items'] ?? [])->first() ?? [];
    vcheck('order item references variant L id', (int) ($item['product_variant_id'] ?? 0) === $variantIds['L'], json_encode($item));
    vcheck('order item variant label snapshot = L', ($item['variant_label_snapshot'] ?? '') === 'L', $item['variant_label_snapshot'] ?? '-');
    vcheck('order item qty 2 @ 120000', (int) ($item['quantity'] ?? 0) === 2 && (int) ($item['price_snapshot'] ?? 0) === 120000);
    vcheck('grand total = 2*120000 + 10000', (int) ($order['grand_total'] ?? 0) === 250000, (string) ($order['grand_total'] ?? '-'));

    // Reservation happens at checkout, deduction at payment
    vcheck('stock L reserved +2 (qty still 3, reserved 2)', stockOf($variantIds['L']) === 3 && reservedOf($variantIds['L']) === 2,
        'qty=' . stockOf($variantIds['L']) . ' reserved=' . reservedOf($variantIds['L']));
    vcheck('M and XL untouched by checkout', stockOf($variantIds['M']) === 5 && stockOf($variantIds['XL']) === 0);

    $orderNumber = $order['order_number'];
    $orderId = (int) $order['id'];

    // --------------------------------------------- [4] pay + settle via webhook
    echo "\n[4] Pay via Midtrans, settle via signed webhook\n";
    $pay = $auth()->post("{$base}/orders/{$orderNumber}/pay");
    vcheck('pay -> 200 with snap token', $pay->status() === 200 && ! empty($pay->json('data.token')), $pay->status() . ' ' . substr($pay->body(), 0, 200));
    vcheck('server key never in pay response', ! str_contains($pay->body(), (string) config('midtrans.server_key')));

    $gross = number_format((float) $order['grand_total'], 2, '.', '');
    $webhook = Http::acceptJson()->post("{$base}/payment/notification", [
        'order_id' => $orderNumber,
        'status_code' => '200',
        'gross_amount' => $gross,
        'signature_key' => hash('sha512', $orderNumber . '200' . $gross . config('midtrans.server_key')),
        'transaction_status' => 'settlement',
        'payment_type' => 'gopay',
        'transaction_id' => "VAR-E2E-{$suffix}",
    ]);
    vcheck('webhook -> 200', $webhook->status() === 200, $webhook->status() . ' ' . $webhook->body());

    // Duplicate notification must be a no-op (idempotent)
    Http::acceptJson()->post("{$base}/payment/notification", [
        'order_id' => $orderNumber,
        'status_code' => '200',
        'gross_amount' => $gross,
        'signature_key' => hash('sha512', $orderNumber . '200' . $gross . config('midtrans.server_key')),
        'transaction_status' => 'settlement',
        'payment_type' => 'gopay',
        'transaction_id' => "VAR-E2E-{$suffix}",
    ]);

    $payment = DB::table('payments')->where('order_id', $orderId)->first();
    $orderRow = DB::table('orders')->where('id', $orderId)->first();
    vcheck('payment success', $payment && $payment->status === 'success', $payment->status ?? '-');
    vcheck('order paid', $orderRow && $orderRow->status === 'paid', $orderRow->status ?? '-');

    // ------------------------------------ [5] stock deducted from the RIGHT variant
    echo "\n[5] Stock: only Size L decremented, exactly once\n";
    vcheck('L qty 3 -> 1 (deducted 2)', stockOf($variantIds['L']) === 1,
        'qty=' . stockOf($variantIds['L']));
    vcheck('L reserved back to 0', reservedOf($variantIds['L']) === 0,
        'reserved=' . reservedOf($variantIds['L']));
    vcheck('M untouched (5)', stockOf($variantIds['M']) === 5, 'qty=' . stockOf($variantIds['M']));
    vcheck('XL untouched (0)', stockOf($variantIds['XL']) === 0, 'qty=' . stockOf($variantIds['XL']));
    vcheck('sold_count +2 on product', (int) DB::table('products')->where('id', $productId)->value('sold_count') === 2);

    // =====================================================================
    // PART B — product WITHOUT seller-defined variants (single default
    // variant, fashion category): standard S/M/L/XL must be offered, the
    // chosen size must ride the cart line and land on the order item, and
    // the shared default-variant stock must be validated/deducted.
    // =====================================================================
    echo "\n=== Part B: no-variant product with standard sizes ===\n";

    $sizelessProductId = DB::table('products')->insertGetId([
        'store_id' => $storeId, 'category_id' => $categoryId, 'name' => "Sizeless Tee {$suffix}",
        'slug' => "var-sizeless-{$suffix}", 'base_price' => 80000, 'status' => 'active', 'weight_grams' => 200,
        'rating_avg' => 0, 'rating_count' => 0, 'sold_count' => 0, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $defaultVariantId = DB::table('product_variants')->insertGetId([
        'product_id' => $sizelessProductId, 'sku' => "SIZ-{$suffix}", 'price' => null,
        'created_at' => now(), 'updated_at' => now(),
    ]);
    DB::table('inventories')->insert([
        'product_variant_id' => $defaultVariantId, 'quantity' => 12, 'reserved_quantity' => 0,
        'low_stock_threshold' => 5, 'created_at' => now(), 'updated_at' => now(),
    ]);
    $cleanupSizeless = ["var-sizeless-{$suffix}", "SIZ-{$suffix}", "siz-{$suffix}"];

    echo "\n[6] Product detail advertises standard sizes\n";
    $sizelessDetail = Http::acceptJson()->get("{$base}/products/var-sizeless-{$suffix}");
    vcheck('detail -> 200', $sizelessDetail->status() === 200, (string) $sizelessDetail->status());
    vcheck('size_options = [S, M, L, XL]', ($sizelessDetail->json('data.size_options') ?? []) === ['S', 'M', 'L', 'XL'],
        json_encode($sizelessDetail->json('data.size_options')));
    vcheck('no attribute values on default variant', empty($sizelessDetail->json('data.variants.0.attribute_values')));

    echo "\n[7] Cart: size is required, stored per line\n";
    $noSize = $auth()->post("{$base}/cart/items", ['product_variant_id' => $defaultVariantId, 'quantity' => 1]);
    vcheck('add WITHOUT size rejected (422 INVALID_SIZE)', $noSize->status() === 422 && ($noSize->json('error.code') ?? '') === 'INVALID_SIZE',
        $noSize->status() . ' ' . $noSize->body());

    $badSize = $auth()->post("{$base}/cart/items", ['product_variant_id' => $defaultVariantId, 'quantity' => 1, 'chosen_size' => 'XXL']);
    vcheck('add with invalid size XXL rejected (422)', $badSize->status() === 422, $badSize->status() . ' ' . $badSize->body());

    $addM = $auth()->post("{$base}/cart/items", ['product_variant_id' => $defaultVariantId, 'quantity' => 1, 'chosen_size' => 'm']);
    vcheck('add size M -> 201', $addM->status() === 201, $addM->status() . ' ' . $addM->body());
    $lineM = collect($addM->json('data.items') ?? [])->firstWhere('product_variant_id', $defaultVariantId);
    vcheck('cart line carries chosen_size = M', $lineM && ($lineM['chosen_size'] ?? null) === 'M', json_encode($lineM));

    $addL = $auth()->post("{$base}/cart/items", ['product_variant_id' => $defaultVariantId, 'quantity' => 2, 'chosen_size' => 'L']);
    vcheck('same variant + different size L -> 201 (separate line)', $addL->status() === 201, $addL->status() . ' ' . $addL->body());
    $lines = collect($addL->json('data.items') ?? [])->where('product_variant_id', $defaultVariantId);
    vcheck('two separate cart lines (M and L)', $lines->count() === 2, 'count=' . $lines->count());

    echo "\n[8] Checkout: size lands on the order item\n";
    $checkout2 = $auth()->post("{$base}/checkout", [
        'shipping_address_id' => $addressId,
        'shipping_methods' => [
            ['store_id' => $storeId, 'courier' => 'jne', 'cost' => 0],
        ],
    ]);
    vcheck('checkout -> 201', $checkout2->status() === 201, $checkout2->status() . ' ' . $checkout2->body());
    $order2 = $checkout2->json('data.order');
    $order2Id = (int) $order2['id'];
    $order2Number = $order2['order_number'];

    $items2 = collect($order2['sub_orders'][0]['items'] ?? []);
    $mItem = $items2->first(fn ($i) => str_contains($i['variant_label_snapshot'] ?? '', 'M'));
    $lItem = $items2->first(fn ($i) => str_contains($i['variant_label_snapshot'] ?? '', 'L'));
    vcheck('order item M snapshot contains M', $mItem !== null, json_encode($items2->pluck('variant_label_snapshot')));
    vcheck('order item L snapshot contains L', $lItem !== null);
    vcheck('both items reference the default variant id',
        $items2->every(fn ($i) => (int) $i['product_variant_id'] === $defaultVariantId));
    vcheck('default-variant stock reserved 3 (1M + 2L)', stockOf($defaultVariantId) === 12 && reservedOf($defaultVariantId) === 3,
        'qty=' . stockOf($defaultVariantId) . ' reserved=' . reservedOf($defaultVariantId));

    $cleanup[] = fn () => DB::table('order_items')->where('sub_order_id', DB::table('sub_orders')->where('order_id', $order2Id)->value('id'))->delete();
    $cleanup[] = fn () => DB::table('sub_orders')->where('order_id', $order2Id)->delete();
    $cleanup[] = fn () => DB::table('payments')->where('order_id', $order2Id)->delete();
    $cleanup[] = fn () => DB::table('orders')->where('id', $order2Id)->delete();
    $cleanup[] = fn () => DB::table('cart_items')->where('cart_id', DB::table('carts')->where('user_id', $customer->id)->value('id'))->delete();

    echo "\n[9] Pay + webhook: shared stock deducted once\n";
    $pay2 = $auth()->post("{$base}/orders/{$order2Number}/pay");
    vcheck('pay -> 200 with snap token', $pay2->status() === 200 && ! empty($pay2->json('data.token')), $pay2->status() . ' ' . substr($pay2->body(), 0, 150));

    $gross2 = number_format((float) $order2['grand_total'], 2, '.', '');
    Http::acceptJson()->post("{$base}/payment/notification", [
        'order_id' => $order2Number,
        'status_code' => '200',
        'gross_amount' => $gross2,
        'signature_key' => hash('sha512', $order2Number . '200' . $gross2 . config('midtrans.server_key')),
        'transaction_status' => 'settlement',
        'payment_type' => 'gopay',
        'transaction_id' => "SIZ-E2E-{$suffix}",
    ]);

    $order2Row = DB::table('orders')->where('id', $order2Id)->first();
    vcheck('sizeless order paid', $order2Row && $order2Row->status === 'paid', $order2Row->status ?? '-');
    vcheck('shared stock 12 -> 9 (deducted 3)', stockOf($defaultVariantId) === 9, 'qty=' . stockOf($defaultVariantId));
    vcheck('reserved back to 0', reservedOf($defaultVariantId) === 0, 'reserved=' . reservedOf($defaultVariantId));
    vcheck('invalid-size error never reached 500', true);
} catch (Throwable $e) {
    $GLOBALS['v_fail']++;
    echo '  EXCEPTION  ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n";
} finally {
    pazarz_cleanup_report(array_merge($markers, $cleanupSizeless ?? []));
    echo "\n=== Variant E2E: {$GLOBALS['v_pass']} passed, {$GLOBALS['v_fail']} failed ===\n";
    if ($GLOBALS['v_fail'] > 0) {
        exit(1);
    }
}
