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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')
                ->constrained('sellers')
                ->restrictOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending',
                'processing',
                'paid',
                'failed',
                'cancelled',
            ])->default('pending');
            $table->string('bank_account_ref')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['seller_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
