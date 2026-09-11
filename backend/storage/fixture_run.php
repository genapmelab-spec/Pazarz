<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Seller;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SubOrder;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

$password = Hash::make("password123");

// ---- seller + store ----
$seller = User::firstOrCreate(["email" => "seller@test.pazarz.co"], [
    "name" => "Seller Test", "email" => "seller@test.pazarz.co", "password" => $password,
    "role_id" => DB::table("roles")->where("name","seller")->value("id"),
]);
$role = Role::where("name","seller")->first();
if ($role) { $seller->syncRoles([$role]); }

$sellerM = Seller::updateOrCreate(["user_id" => $seller->id], [
    "user_id" => $seller->id, "business_name" => "Toko Test", "business_type" => "Umum",
    "tax_id" => null, "verification_status" => "verified", "verified_at" => now(),
    "commission_rate" => 0,
]);

$store = Store::firstOrCreate(["seller_id" => $sellerM->id], [
    "seller_id" => $sellerM->id, "name" => "Test Toko",
    "slug" => Str::slug("test-toko"), "logo_url" => null, "banner_url" => null,
    "description" => "Toko uji", "rating_avg" => 0, "rating_count" => 0, "status" => "active",
    "address_id" => null,
]);

// ---- customer ----
$customer = User::updateOrCreate(["email" => "dwight@test.pazarz.co"], [
    "name" => "Dwight Checkout", "email" => "dwight@test.pazarz.co", "password" => $password,
    "phone" => null, "status" => "active",
]);

// ---- address ----
$address = Address::updateOrCreate([
    "addressable_type" => "App\Models\User",
    "addressable_id" => $customer->id,
    "is_default" => true,
], [
    "addressable_type" => "App\Models\User",
    "addressable_id" => $customer->id,
    "label" => "Rumah",
    "recipient_name" => "Dwight Checkout",
    "phone" => "081200000001",
    "province" => "Jawa Barat",
    "city" => "Bandung",
    "district" => "Cibiru",
    "postal_code" => "40234",
    "full_address" => "Jl. Contoh No. 1, Rancasari",
    "latitude" => null,
    "longitude" => null,
    "is_default" => true,
]);

// ---- product/variant/inventory ----
$cat = Category::where("slug", "elektronik")->firstOrFail();
$prod = Product::firstOrCreate(["store_id" => $store->id, "slug" => "telur-premium-pack"], [
    "store_id" => $store->id, "name" => "Telur Premium Pack", "slug" => "telur-premium-pack",
    "description" => "Telur premium segar", "base_price" => 12000, "status" => "active",
    "category_id" => $cat->id, "weight_grams" => 1200, "rating_avg" => 0, "rating_count" => 0,
    "sold_count" => 0,
]);
$variant = ProductVariant::firstOrCreate(["product_id" => $prod->id], [
    "product_id" => $prod->id, "sku" => "TVGPP-001", "price" => 12000, "image_id" => null,
]);

Inventory::updateOrCreate(["product_variant_id" => $variant->id], [
    "product_variant_id" => $variant->id, "quantity" => 20, "reserved_quantity" => 0,
    "low_stock_threshold" => 2,
]);

// ---- cart line (real model may differ; only create if table has product_variant_id) ----
$cartColumns = DB::getSchemaBuilder()->getColumnListing("carts");
if (in_array("product_variant_id", $cartColumns, true)) {
    DB::table("carts")->updateOrInsert(["user_id" => $customer->id, "product_variant_id" => $variant->id], [
        "user_id" => $customer->id, "product_variant_id" => $variant->id,
        "updated_at" => now(),
    ]);
}

// ---- clean prior orders ----
$existingOrders = Order::where("user_id", $customer->id)->pluck("id");
if ($existingOrders->isNotEmpty()) {
    DB::table("order_items")->whereIn("sub_order_id", $existingOrders)->delete();
    // order_items may reference sub_order_id or order_id depending on schema; also clear any order_id linkage
    DB::table("payments")->whereIn("order_id", $existingOrders)->delete();
    Order::where("user_id", $customer->id)->delete();
}

$subtotal = $variant->price * 2; // qty 2
$shipping = 0;
$grandTotal = $subtotal + $shipping;

// ---- order ----
$order = Order::create([
    "order_number" => "ORD-".Str::upper(Str::random(12)),
    "user_id" => $customer->id,
    "shipping_address_id" => $address->id,
    "subtotal" => $subtotal,
    "shipping_total" => $shipping,
    "discount_total" => 0,
    "grand_total" => $grandTotal,
    "status" => "pending",
    "placed_at" => now(),
]);

// ---- sub order (order_items.sub_order_id FK -> sub_orders.id) ----
$subOrder = SubOrder::create([
    "order_id" => $order->id,
    "store_id" => $store->id,
    "subtotal" => $subtotal,
    "shipping_cost" => $shipping,
    "status" => "pending",
]);

// ---- order item ----
OrderItem::create([
    "sub_order_id" => $subOrder->id,
    "product_variant_id" => $variant->id,
    "product_name_snapshot" => $prod->name,
    "variant_label_snapshot" => $variant->name ?? $variant->sku,
    "price_snapshot" => $variant->price,
    "quantity" => 2,
    "subtotal" => $subtotal,
]);

// ---- payment record ----
$providerRef = "FIXTURE-ORD-{$order->id}-".Str::upper(Str::random(6));
$pay = Payment::updateOrCreate(["order_id" => $order->id], [
    "order_id" => $order->id,
    "method" => "midtrans",
    "provider" => "midtrans",
    "provider_reference" => $providerRef,
    "amount" => $grandTotal,
    "status" => "unpaid",
    "paid_at" => null,
]);

echo json_encode([
    "sellerId" => $seller->id,
    "storeId" => $store->id,
    "productId" => $prod->id,
    "variantId" => $variant->id,
    "customerId" => $customer->id,
    "addressId" => $address->id,
    "orderId" => $order->id,
    "orderNumber" => $order->order_number,
    "subOrderId" => $subOrder->id,
    "paymentId" => $pay->id,
    "providerRef" => $providerRef,
    "grandTotal" => $grandTotal,
    "cartPresent" => in_array("product_variant_id", $cartColumns, true),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
