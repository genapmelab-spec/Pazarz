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
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('user_id')
        ->constrained('users')
        ->cascadeOnDelete();

            $table->string('store_name');
            $table->string('store_slug')->unique();
            $table->text('description')->nullable();

            $table->enum('verification_status', [
                'pending',
                'verified',
                'rejected',
            ])->default('pending');

            $table->text('verification_notes')->nullable();

            $table->decimal('rating_avg', 3, 2)->default(0);

            $table->decimal('commission_override_pct', 5, 2)
                ->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};