<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Seed roles and permissions via Spatie
        $this->call(PermissionSeeder::class);

        // Create Admin
        $admin = User::create([
            'name' => 'Admin Pazarz',
            'email' => 'admin@pazarz.com',
            'password' => Hash::make('password'),
            'role_id' => 'admin',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create Customer
        $customer = User::create([
            'name' => 'Dinda Customer',
            'email' => 'customer@pazarz.com',
            'password' => Hash::make('password'),
            'role_id' => 'customer',
            'email_verified_at' => now(),
        ]);
        $customer->assignRole('customer');

        $customerAddress = $customer->addresses()->create([
            'addressable_type' => User::class,
            'addressable_id' => $customer->id,
            'label' => 'Home',
            'recipient_name' => 'Dinda',
            'phone' => '081234567890',
            'province' => 'DKI Jakarta',
            'city' => 'Jakarta Selatan',
            'district' => 'Kebayoran Baru',
            'postal_code' => '12190',
            'full_address' => 'Jl. Senopati No. 123',
            'is_default' => true,
        ]);

        // Create Sellers
        $sellers = [];
        $storeNames = ['Urban Threads', 'Street Culture ID', 'Minimalist Co'];

        foreach ($storeNames as $index => $storeName) {
            $sellerUser = User::create([
                'name' => 'Seller ' . ($index + 1),
                'email' => 'seller' . ($index + 1) . '@pazarz.com',
                'password' => Hash::make('password'),
                'role_id' => 'seller',
                'email_verified_at' => now(),
            ]);
            $sellerUser->assignRole('seller');

            $seller = Seller::create([
                'user_id' => $sellerUser->id,
                'business_name' => $storeName . ' Store',
                'verification_status' => 'verified',
                'verified_at' => now(),
                'commission_rate' => 5.00,
            ]);

            $store = Store::create([
                'seller_id' => $seller->id,
                'name' => $storeName,
                'slug' => Str::slug($storeName),
                'description' => "Premium quality products from {$storeName}",
                'rating_avg' => rand(35, 50) / 10,
                'rating_count' => rand(10, 100),
            ]);

            $sellers[] = ['user' => $sellerUser, 'seller' => $seller, 'store' => $store];
        }

        // Create Categories
        $categories = ['Streetwear', 'Minimalist', 'Accessories', 'Footwear', 'Outerwear'];
        $createdCategories = [];

        foreach ($categories as $catName) {
            $cat = Category::create([
                'name' => $catName,
                'slug' => Str::slug($catName),
                'is_active' => true,
                'sort_order' => array_search($catName, $categories),
            ]);
            $createdCategories[] = $cat;
        }

        // Create Product Attributes
        $colorAttr = ProductAttribute::create(['name' => 'Color']);
        $sizeAttr = ProductAttribute::create(['name' => 'Size']);

        // Create Products
        $productNames = [
            'Premium Oversized Tee', 'Classic Hoodie', 'Slim Fit Chinos', 'Minimal Watch',
            'Canvas Sneakers', 'Tech Bomber Jacket', 'Bamboo Sunglasses', 'Raw Denim Jacket',
            'Linen Camp Collar Shirt', 'Corduroy Bucket Hat'
        ];

        foreach ($productNames as $index => $productName) {
            $sellerData = $sellers[$index % count($sellers)];
            $category = $createdCategories[$index % count($createdCategories)];

            $product = Product::create([
                'store_id' => $sellerData['store']->id,
                'category_id' => $category->id,
                'name' => $productName,
                'slug' => Str::slug($productName) . '-' . $index,
                'description' => "High quality {$productName} from {$sellerData['store']->name}. Crafted with premium materials for lasting comfort and style.",
                'base_price' => rand(15, 50) * 10000,
                'status' => 'active',
                'weight_grams' => rand(100, 1000),
                'sold_count' => rand(0, 200),
            ]);

            // Create product images
            ProductImage::create([
                'product_id' => $product->id,
                'url' => "https://placehold.co/600x800/f5f5f5/111111?text=" . urlencode($productName),
                'sort_order' => 0,
                'is_primary' => true,
            ]);

            // Create variants
            $colors = ['Black', 'White', 'Navy'];
            $sizes = ['S', 'M', 'L', 'XL'];

            foreach ($sizes as $size) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper(Str::random(3) . '-' . $size . '-' . $index),
                    'price' => $size === 'XL' ? $product->base_price + 20000 : null,
                ]);

                Inventory::create([
                    'product_variant_id' => $variant->id,
                    'quantity' => rand(5, 100),
                    'low_stock_threshold' => 5,
                ]);

                ProductAttributeValue::create([
                    'product_attribute_id' => $sizeAttr->id,
                    'product_variant_id' => $variant->id,
                    'value' => $size,
                ]);
            }
        }

        // Create a pending seller for testing
        $pendingUser = User::create([
            'name' => 'Pending Seller',
            'email' => 'pending@pazarz.com',
            'password' => Hash::make('password'),
            'role_id' => 'seller',
            'email_verified_at' => now(),
        ]);
        $pendingUser->assignRole('seller');

        $pendingSeller = Seller::create([
            'user_id' => $pendingUser->id,
            'business_name' => 'Pending Store',
            'verification_status' => 'pending',
        ]);

        Store::create([
            'seller_id' => $pendingSeller->id,
            'name' => 'Pending Store',
            'slug' => 'pending-store-' . Str::random(5),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@pazarz.com / password');
        $this->command->info('Customer: customer@pazarz.com / password');
        $this->command->info('Seller: seller1@pazarz.com / password');
    }
}
