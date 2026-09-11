use App\Models\User;
use App\Models\Seller;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

$password = Hash::make("password123");

$seller = User::firstOrCreate(["email" => "seller@test.pazarz.co"], [
    "name" => "Seller Test", "email" => "seller@test.pazarz.co", "password" => $password,
]);
$role = Role::where("name", "seller")->first();
if ($role) { $seller->syncRoles([$role]); }

$sellerM = Seller::updateOrCreate(["user_id" => $seller->id], [
    "user_id" => $seller->id, "business_name" => "Toko Test", "status" => "approved",
    "verification_status" => "verified", "nin" => "0000000000000000", "bank_name" => "Bank BCA",
    "bank_account" => "1234567890", "bank_account_name" => "Seller Test", "address" => "alamat test",
    "city" => "kota", "phone" => "081200000000",
]);

$store = Store::firstOrCreate(["seller_id" => $sellerM->id], [
    "seller_id" => $sellerM->id, "name" => "Test Toko",
    "slug" => Str::slug("test-toko"), "is_active" => true,
]);

$customer = User::updateOrCreate(["email" => "dwight@test.pazarz.co"], [
    "name" => "Dwight Checkout", "email" => "dwight@test.pazarz.co", "password" => $password,
]);

$cat = Category::where("slug", "elektronik")->firstOrFail();
$prod = Product::firstOrCreate(["store_id" => $store->id, "sku" => "TEST-EGG-001"], [
    "store_id" => $store->id, "name" => "Telur Premium Pack", "slug" => "telur-premium-pack",
    "description" => "Telur premium segar", "price" => 12000, "stock" => 20, "status" => "active",
    "category_id" => $cat->id, "is_active" => true,
]);
$variant = ProductVariant::firstOrCreate(["product_id" => $prod->id], [
    "product_id" => $prod->id, "sku" => "TVGPP-001", "name" => "Pack 12 Butir",
    "quantity" => 20, "price" => 12000, "additional_price" => 0,
]);
Inventory::firstOrCreate(["variant_id" => $variant->id], [
    "variant_id" => $variant->id, "quantity" => 20, "low_stock_threshold" => 2,
]);

$cart = Cart::updateOrCreate(["user_id" => $customer->id, "product_variant_id" => $variant->id], [
    "user_id" => $customer->id, "product_variant_id" => $variant->id, "quantity" => 2,
]);

// Address for checkout
$address = Address::firstOrCreate(["user_id" => $customer->id, "is_default" => true], [
    "user_id" => $customer->id, "name" => "Dwight Checkout", "phone" => "081200000001",
    "province" => "Jawa Barat", "city" => "Bandung", "district" => "Cibiru", "village" => "Rancasari",
    "address" => "Jl. Contoh No. 1", "postal_code" => "40234", "is_default" => true,
]);

// Clean previous orders so this fixture is idempotent
DB::table("order_items")->whereIn("order_id", function ($q) { $q->select("id")->from("orders")->where("user_id", $customer->id); })->delete();
Order::where("user_id", $customer->id)->delete();

// Create checkout payload snapshot and print ids
$total = $variant->price * $cart->quantity;
echo json_encode([
    "sellerId" => $seller->id,
    "sellerMidId" => $sellerM->id,
    "storeId" => $store->id,
    "productId" => $prod->id,
    "variantId" => $variant->id,
    "customerId" => $customer->id,
    "cartId" => $cart->id,
    "addressId" => $address->id,
    "total" => $total,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
