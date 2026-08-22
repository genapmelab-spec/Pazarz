<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()
                ->constrained('stores')->nullOnDelete(); // null = platform-wide
            $table->string('code')->unique();
            $table->string('type'); // percentage|fixed
            $table->decimal('value', 14, 2);
            $table->decimal('min_spend', 14, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
