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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('shipping_address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();
            $table->decimal('grand_total', 12, 2);
            $table->enum('status', [
                'pending',
                'paid',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'completed',
            ])->default('pending');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
