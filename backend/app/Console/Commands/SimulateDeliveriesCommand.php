<?php

namespace App\Console\Commands;

use App\Services\ShippingSimulationService;
use Illuminate\Console\Command;

class SimulateDeliveriesCommand extends Command
{
    protected $signature = 'pazarz:simulate-deliveries';

    protected $description = 'Advance the mock shipping simulation — deliver shipments whose transit time has elapsed';

    public function handle(ShippingSimulationService $simulation): int
    {
        $delivered = $simulation->runSimulation();

        if ($delivered > 0) {
            $this->info("Delivered {$delivered} shipment(s).");
        } else {
            $this->line('No shipments due for delivery.');
        }

        return self::SUCCESS;
    }
}
