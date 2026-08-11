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
        Schema::create('payout_items', function (Blueprint $table) {
             $table->id();
            $table->foreignId('payout_id')
                ->constrained('payouts')
                ->cascadeOnDelete();
            $table->foreignId('sub_order_id')
                ->constrained('sub_orders')
                ->restrictOnDelete();
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->index('payout_id');
            $table->index('sub_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payout_items');
    }
};
