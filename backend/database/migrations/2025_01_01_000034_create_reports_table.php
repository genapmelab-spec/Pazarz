<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            // Polymorphic target: Product|Store|Review
            $table->unsignedBigInteger('reportable_id');
            $table->string('reportable_type');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('open')->index(); // open|reviewed|dismissed|actioned
            $table->timestamps();

            $table->index(['reportable_id', 'reportable_type'], 'reports_reportable_index');
            $table->index('reporter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
