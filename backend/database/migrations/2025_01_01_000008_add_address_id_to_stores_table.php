<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// This migration adds `stores.address_id` after the `addresses` table
// exists, because ERD.md defines a direct FK from stores -> addresses
// (store's shipping-origin address) while DATABASE.md's ordering guide
// creates `addresses` after `stores`. Splitting the column addition
// into its own migration keeps the documented table-creation order
// intact while still satisfying FK integrity.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('address_id')->nullable()->after('seller_id')
                ->constrained('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('address_id');
        });
    }
};
