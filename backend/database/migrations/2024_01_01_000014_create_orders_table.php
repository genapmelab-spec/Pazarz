<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('shipping_address_id')->constrained('addresses');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_total', 12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('status')->default('pending_payment'); // pending_payment/paid/processing/completed/cancelled
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
        });

        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending/confirmed/processing/shipped/completed/cancelled
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('store_id');
            $table->index('status');
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained();
            $table->string('product_name_snapshot');
            $table->string('variant_label_snapshot')->nullable();
            $table->decimal('price_snapshot', 12, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();

            $table->index('sub_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('sub_orders');
        Schema::dropIfExists('orders');
    }
};
