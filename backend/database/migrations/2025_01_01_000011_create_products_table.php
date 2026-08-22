<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->decimal('base_price', 14, 2);
            $table->string('status')->default('draft')->index(); // draft|active|inactive|archived
            $table->unsignedInteger('weight_grams')->default(0);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // slug unique per store, not globally unique
            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'status']);
            $table->index('category_id');
            $table->fullText('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
