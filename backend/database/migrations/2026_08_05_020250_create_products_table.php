<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('sellers')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', [
                'draft',
                'active',
                'inactive',
                'archived',
            ])->default('draft');

            $table->decimal('base_price', 12, 2);
            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->timestamps();
            $table->index(['seller_id', 'status']);
            $table->index(['category_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
