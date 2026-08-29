<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissionGroups = [
            'user' => ['view-users', 'manage-users', 'suspend-users'],
            'seller' => ['view-sellers', 'verify-sellers', 'manage-sellers'],
            'store' => ['manage-own-store', 'view-all-stores'],
            'product' => ['manage-own-products', 'view-all-products', 'moderate-products'],
            'category' => ['manage-categories'],
            'order' => ['view-own-orders', 'view-own-sub-orders', 'process-own-sub-orders', 'view-all-orders'],
            'payment' => ['view-own-payments', 'view-all-payments'],
            'review' => ['create-review', 'reply-review', 'moderate-reviews'],
            'promotion' => ['manage-own-promotions', 'manage-platform-promotions'],
            'dispute' => ['raise-dispute', 'respond-dispute', 'mediate-disputes'],
            'report' => ['submit-report', 'review-reports'],
            'analytics' => ['view-own-store-analytics', 'view-platform-analytics'],
            'platform' => ['manage-platform-settings', 'view-audit-logs'],
        ];

        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::create(['name' => $permission, 'guard_name' => 'web']);
            }
        }

        // Create Roles
        $customerRole = Role::create(['name' => 'customer', 'guard_name' => 'web']);
        $customerRole->givePermissionTo([
            'view-own-orders',
            'create-review',
            'raise-dispute',
            'submit-report',
        ]);

        $sellerRole = Role::create(['name' => 'seller', 'guard_name' => 'web']);
        $sellerRole->givePermissionTo([
            'manage-own-store',
            'manage-own-products',
            'view-own-sub-orders',
            'process-own-sub-orders',
            'reply-review',
            'manage-own-promotions',
            'respond-dispute',
            'view-own-store-analytics',
        ]);

        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->givePermissionTo(Permission::all());
    }
}
