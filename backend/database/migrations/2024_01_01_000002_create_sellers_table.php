<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->unique();
            $table->string('business_name');
            $table->string('business_type')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('verification_status')->default('pending'); // pending/verified/rejected
            $table->timestamp('verified_at')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->timestamps();
            $table->softDeletes();

            $table->index('verification_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
