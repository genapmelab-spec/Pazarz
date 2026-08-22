<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->unique()->constrained('sub_orders')->cascadeOnDelete();
            $table->string('courier');
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending')->index();
            // pending|picked_up|in_transit|delivered|failed
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
