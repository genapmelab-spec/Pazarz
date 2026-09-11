<?php

/**
 * FK-safe cleanup of test fixtures created by the Midtrans test scripts.
 *
 * Direct run:
 *   php artisan tinker --execute="require 'storage/cleanup_fixtures.php';"
 *
 * From another script:
 *   define('PAZARZ_CLEANUP_MANUAL', true);
 *   require 'storage/cleanup_fixtures.php';
 *   pazarz_cleanup_report(['mt-']);
 *
 * Deletes children before parents so foreign keys never abort the teardown.
 */

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (! function_exists('pazarz_cleanup')) {
    function pazarz_cleanup(array $markers): array
    {
        $report = [];

        $like = fn (array $cols) => function ($q) use ($cols, $markers) {
            foreach ($markers as $m) {
                foreach ($cols as $c) {
                    $q->orWhere($c, 'like', $m . '%');
                }
            }
        };

        $userIds = DB::table('users')->where($like(['email']))->pluck('id');
        $storeIds = DB::table('stores')->where($like(['slug']))->pluck('id');
        $productIds = DB::table('products')->where($like(['slug']))->pluck('id');
        $categoryIds = DB::table('categories')->where($like(['slug']))->pluck('id');

        $orderIds = DB::table('orders')->whereIn('user_id', $userIds)->pluck('id');
        $subOrderIds = DB::table('sub_orders')
            ->whereIn('order_id', $orderIds)
            ->orWhereIn('store_id', $storeIds)
            ->pluck('id');
        $shipmentIds = DB::table('shipments')->whereIn('sub_order_id', $subOrderIds)->pluck('id');
        $variantIds = DB::table('product_variants')->whereIn('product_id', $productIds)->pluck('id');
        $cartIds = DB::table('carts')->whereIn('user_id', $userIds)->pluck('id');
        $reviewIds = Schema::hasTable('reviews')
            ? DB::table('reviews')->whereIn('user_id', $userIds)->pluck('id')
            : collect();

        $steps = [
            'shipment_tracking_events' => fn () => Schema::hasTable('shipment_tracking_events')
                ? DB::table('shipment_tracking_events')->whereIn('shipment_id', $shipmentIds)->delete() : 0,
            'shipments' => fn () => DB::table('shipments')->whereIn('sub_order_id', $subOrderIds)->delete(),
            'order_items' => fn () => DB::table('order_items')->whereIn('sub_order_id', $subOrderIds)->delete(),
            'sub_orders' => fn () => DB::table('sub_orders')->whereIn('id', $subOrderIds)->delete(),
            'payments' => fn () => DB::table('payments')->whereIn('order_id', $orderIds)->delete(),
            'coupon_usages' => fn () => Schema::hasTable('coupon_usages')
                ? DB::table('coupon_usages')->whereIn('order_id', $orderIds)->delete() : 0,
            'orders' => fn () => DB::table('orders')->whereIn('id', $orderIds)->delete(),
            'cart_items' => fn () => DB::table('cart_items')->whereIn('cart_id', $cartIds)->delete(),
            'carts' => fn () => DB::table('carts')->whereIn('id', $cartIds)->delete(),
            'addresses' => fn () => DB::table('addresses')->where('addressable_type', User::class)
                ->whereIn('addressable_id', $userIds)->delete(),
            'inventories' => fn () => DB::table('inventories')->whereIn('product_variant_id', $variantIds)->delete(),
            'product_variants' => fn () => DB::table('product_variants')->whereIn('id', $variantIds)->delete(),
            'products' => fn () => DB::table('products')->whereIn('id', $productIds)->delete(),
            'stores' => fn () => DB::table('stores')->whereIn('id', $storeIds)->delete(),
            'sellers' => fn () => DB::table('sellers')->whereIn('user_id', $userIds)->delete(),
            'categories' => fn () => DB::table('categories')->whereIn('id', $categoryIds)->delete(),
            'notifications' => fn () => DB::table('notifications')
                ->where('notifiable_type', User::class)->whereIn('notifiable_id', $userIds)->delete(),
            'model_has_roles' => fn () => Schema::hasTable('model_has_roles')
                ? DB::table('model_has_roles')->whereIn('model_id', $userIds)->delete() : 0,
            'personal_access_tokens' => fn () => Schema::hasTable('personal_access_tokens')
                ? DB::table('personal_access_tokens')->whereIn('tokenable_id', $userIds)->delete() : 0,
            'review_images' => fn () => Schema::hasTable('review_images')
                ? DB::table('review_images')->whereIn('review_id', $reviewIds)->delete() : 0,
            'reviews' => fn () => Schema::hasTable('reviews')
                ? DB::table('reviews')->whereIn('user_id', $userIds)->delete() : 0,
            'seller_followers' => fn () => Schema::hasTable('seller_followers')
                ? DB::table('seller_followers')->whereIn('user_id', $userIds)->delete() : 0,
            'wishlists' => fn () => Schema::hasTable('wishlists')
                ? DB::table('wishlists')->whereIn('user_id', $userIds)->delete() : 0,
            'sessions' => fn () => Schema::hasTable('sessions')
                ? DB::table('sessions')->whereIn('user_id', $userIds)->delete() : 0,
            'users' => fn () => DB::table('users')->whereIn('id', $userIds)->delete(),
        ];

        foreach ($steps as $name => $step) {
            try {
                $report[$name] = $step();
            } catch (\Throwable $e) {
                $report[$name] = 'ERROR: ' . $e->getMessage();
            }
        }

        return $report;
    }
}

if (! function_exists('pazarz_cleanup_report')) {
    function pazarz_cleanup_report(array $markers): void
    {
        $report = pazarz_cleanup($markers);

        echo "\n=== fixture cleanup (" . implode(', ', $markers) . ") ===\n";

        foreach ($report as $table => $count) {
            if ($count !== 0) {
                echo "  {$table}: {$count}\n";
            }
        }

        $remaining = 0;
        foreach ($markers as $m) {
            $remaining += DB::table('users')->where('email', 'like', $m . '%')->count()
                + DB::table('products')->where('slug', 'like', $m . '%')->count()
                + DB::table('stores')->where('slug', 'like', $m . '%')->count();
        }

        echo "  remaining fixture rows: {$remaining}\n";
    }
}

if (! defined('PAZARZ_CLEANUP_MANUAL')) {
    pazarz_cleanup_report(['mt-', 'ht-', 'sg-']);
}
