<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained();
            $table->string('name');
            $table->string('type'); // percentage/fixed
            $table->decimal('value', 12, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status')->default('active'); // active/inactive/expired
            $table->timestamps();
        });

        Schema::create('promotion_products', function (Blueprint $table) {
            $table->foreignId('promotion_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['promotion_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
