<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Simulation state: pending / picked_up / in_transit / delivered / failed
            // (existing column reused; simulated flags below)
            $table->timestamp('estimated_delivery_at')->nullable()->after('delivered_at');
            $table->timestamp('simulation_started_at')->nullable()->after('estimated_delivery_at');
            $table->string('simulation_state')->nullable()->after('simulation_started_at'); // queued/running/done
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_at', 'simulation_started_at', 'simulation_state']);
        });
    }
};
