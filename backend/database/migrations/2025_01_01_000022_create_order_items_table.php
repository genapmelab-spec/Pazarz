<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained('sub_orders')->cascadeOnDelete();
            // No cascade delete against product_variants: history must survive
            // even if a variant is later removed (products use soft delete).
            $table->foreignId('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->string('product_name_snapshot');
            $table->string('variant_label_snapshot')->nullable();
            $table->decimal('price_snapshot', 14, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();

            $table->index('sub_order_id');
            $table->index('product_variant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
