<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ShipmentTrackingEvent;
use App\Models\SubOrder;
use Illuminate\Support\Facades\DB;

class ShippingSimulationService
{
    /**
     * Ship a sub-order with a mock courier: generates a dummy tracking number
     * (PZR-YYYYMMDD-####), creates the Shipment, and starts the delivery
     * simulation. No external courier API is involved.
     */
    public function shipSubOrder(SubOrder $subOrder, string $courier): Shipment
    {
        if (!$subOrder->canTransitionTo('shipped')) {
            throw new \Exception('Cannot ship this sub-order from current status.');
        }

        return DB::transaction(function () use ($subOrder, $courier) {
            $subOrder->update(['status' => 'shipped']);

            $shipment = $subOrder->shipment()->create([
                'courier' => $courier,
                'tracking_number' => $this->generateTrackingNumber(),
                'status' => 'in_transit',
                'shipped_at' => now(),
                'estimated_delivery_at' => now()->addMinutes((int) config('shipping.simulation.transit_minutes', 2)),
                'simulation_started_at' => now(),
                'simulation_state' => 'running',
            ]);

            $this->logEvent($shipment, 'picked_up', 'Paket diambil kurir', $subOrder->store->name);

            $this->logEvent(
                $shipment,
                'in_transit',
                'Paket dalam perjalanan menuju pembeli',
                'Hub Pazarz Express'
            );

            // Sync parent order status
            app(OrderService::class)->syncOrderStatus($subOrder->order);

            \App\Models\Notification::createForUser(
                $subOrder->order->user,
                'order_shipped',
                'Order Shipped',
                "Your order #{$subOrder->order->order_number} from {$subOrder->store->name} has been shipped. Tracking: {$shipment->tracking_number}",
                ['order_id' => $subOrder->order_id, 'sub_order_id' => $subOrder->id, 'tracking_number' => $shipment->tracking_number]
            );

            return $shipment;
        });
    }

    /**
     * Advance all running simulations. A shipment whose transit time has
     * elapsed is marked delivered, its sub-order completed, and the parent
     * order completed when every sub-order is done. Called by the scheduler
     * every minute and by the admin "advance simulation" action.
     */
    public function runSimulation(): int
    {
        $transitMinutes = (int) config('shipping.simulation.transit_minutes', 2);

        $dueShipments = Shipment::with('subOrder.store')
            ->where('simulation_state', 'running')
            ->where('status', 'in_transit')
            ->where('simulation_started_at', '<=', now()->subMinutes($transitMinutes))
            ->get();

        $deliveredCount = 0;

        foreach ($dueShipments as $shipment) {
            $this->deliverShipment($shipment);
            $deliveredCount++;
        }

        return $deliveredCount;
    }

    /**
     * Deliver a single shipment: shipment → delivered, sub-order → completed,
     * parent order → completed when all sub-orders are done. No customer
     * action is required anywhere in this chain.
     */
    public function deliverShipment(Shipment $shipment): void
    {
        DB::transaction(function () use ($shipment) {
            if ($shipment->status === 'delivered') {
                return; // idempotent
            }

            $subOrder = $shipment->subOrder;

            $shipment->update([
                'status' => 'delivered',
                'delivered_at' => now(),
                'simulation_state' => 'done',
            ]);

            $this->logEvent($shipment, 'delivered', 'Paket telah diterima', $subOrder->store->name);

            if ($subOrder->canTransitionTo('completed')) {
                $subOrder->update(['status' => 'completed']);
                app(OrderService::class)->onSubOrderCompleted($subOrder);
            }

            \App\Models\Notification::createForUser(
                $subOrder->order->user,
                'order_delivered',
                'Order Delivered',
                "Your order #{$subOrder->order->order_number} from {$subOrder->store->name} has been delivered.",
                ['order_id' => $subOrder->order_id, 'sub_order_id' => $subOrder->id]
            );
        });
    }

    /**
     * Dummy tracking number: PZR-20260907-0001 style.
     */
    public function generateTrackingNumber(): string
    {
        $prefix = 'PZR-'.now()->format('Ymd').'-';

        $last = Shipment::where('tracking_number', 'like', $prefix.'%')
            ->orderByDesc('tracking_number')
            ->value('tracking_number');

        $seq = $last ? ((int) substr($last, strrpos($last, '-') + 1)) + 1 : 1;

        // Handle collisions defensively (e.g. after data resets)
        while (Shipment::where('tracking_number', $prefix.sprintf('%04d', $seq))->exists()) {
            $seq++;
        }

        return $prefix.sprintf('%04d', $seq);
    }

    protected function logEvent(Shipment $shipment, string $status, string $description, string $location): void
    {
        ShipmentTrackingEvent::create([
            'shipment_id' => $shipment->id,
            'status' => $status,
            'description' => $description,
            'location' => $location,
            'occurred_at' => now(),
        ]);
    }
}
