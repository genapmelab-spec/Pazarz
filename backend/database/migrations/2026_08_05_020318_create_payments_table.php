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
        Schema::create('payments', function (Blueprint $table) {
             $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('gateway');
            $table->string('gateway_ref')->nullable();

            $table->string('method');

            $table->decimal('amount', 12, 2);

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'cancelled',
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('gateway_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
