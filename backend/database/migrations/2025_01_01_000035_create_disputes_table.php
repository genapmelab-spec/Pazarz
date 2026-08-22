<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_order_id')->constrained('sub_orders')->cascadeOnDelete();
            $table->foreignId('raised_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason');
            $table->string('status')->default('open')->index(); // open|in_review|resolved|rejected
            $table->text('resolution')->nullable();
            $table->foreignId('resolved_by')->nullable()
                ->constrained('users')->nullOnDelete(); // admin who resolved it
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('sub_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
