<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('type'); // percentage/fixed
            $table->decimal('value', 12, 2);
            $table->decimal('min_spend', 12, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('active'); // active/inactive/expired
            $table->timestamps();
        });

        Schema::create('coupon_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('order_id')->constrained();
            $table->timestamp('used_at');
            $table->timestamps();

            $table->unique(['coupon_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
