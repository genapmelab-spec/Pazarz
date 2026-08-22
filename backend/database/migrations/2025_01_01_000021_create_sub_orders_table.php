<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->restrictOnDelete();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('shipping_cost', 14, 2)->default(0);
            $table->string('status')->default('pending')->index();
            // pending|confirmed|processing|shipped|completed|cancelled
            $table->string('cancelled_reason')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('store_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_orders');
    }
};
