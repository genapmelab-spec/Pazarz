<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->unique();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->unsignedTinyInteger('rating'); // 1-5
            $table->text('comment')->nullable();
            $table->text('seller_reply')->nullable();
            $table->string('status')->default('visible'); // visible/hidden/flagged
            $table->timestamps();

            $table->index('product_id');
            $table->index('user_id');
        });

        Schema::create('review_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_images');
        Schema::dropIfExists('reviews');
    }
};
