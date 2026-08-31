<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        $this->command->info('Database seeded successfully!');
        $this->command->info('Admin: admin@pazarz.com / password');
    }
}
