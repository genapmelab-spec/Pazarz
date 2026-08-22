<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            // Polymorphic owner: User or Store
            $table->unsignedBigInteger('addressable_id');
            $table->string('addressable_type');
            $table->string('label')->nullable(); // e.g. "Rumah", "Kantor"
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('province');
            $table->string('city');
            $table->string('district');
            $table->string('postal_code');
            $table->text('full_address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['addressable_id', 'addressable_type'], 'addresses_addressable_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
