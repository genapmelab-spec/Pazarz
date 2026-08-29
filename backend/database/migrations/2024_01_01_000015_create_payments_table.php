<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->unique();
            $table->string('method'); // va/e-wallet/card
            $table->string('provider')->nullable();
            $table->string('provider_reference')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending/success/failed/refunded
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('provider_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
