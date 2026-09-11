<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\SubOrder;
use App\Services\OrderService;
use App\Services\ShippingSimulationService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShippingSimulationService $simulation
    ) {}

    public function index(Request $request)
    {
        $subOrders = $this->orderService->getSellerSubOrders($request->user(), $request->status);

        return view('seller.orders.index', compact('subOrders'));
    }

    public function show(SubOrder $subOrder)
    {
        $this->authorize('view', $subOrder);
        $subOrder->load(['order.user', 'order.shippingAddress', 'items.variant.product', 'store', 'shipment.trackingEvents']);

        return view('seller.orders.show', compact('subOrder'));
    }

    /**
     * Confirm AND ship the sub-order in one action: the shipment (with a
     * generated dummy tracking number) is created immediately and the
     * automatic delivery simulation starts. The seller never fills in
     * courier/tracking manually. Safe to call on legacy confirmed/processing
     * sub-orders and idempotent when the order is already shipped.
     */
    public function confirm(SubOrder $subOrder)
    {
        $this->authorize('confirm', $subOrder);

        // Sellers may only act once the customer's Midtrans payment settled.
        if (in_array($subOrder->order->status, ['pending_payment', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'This order has not been paid yet.');
        }

        try {
            if ($subOrder->status === 'pending') {
                $this->orderService->confirmSubOrder($subOrder);
                $subOrder = $subOrder->refresh();
            }

            if ($subOrder->status === 'shipped' && $subOrder->shipment) {
                return redirect()->back()->with('success', 'Order already shipped. Tracking: '.$subOrder->shipment->tracking_number);
            }

            $shipment = $this->simulation->shipSubOrder($subOrder, config('shipping.mock.courier', 'Pazarz Express'));

            return redirect()->back()->with('success', 'Order confirmed & shipped. Tracking: '.$shipment->tracking_number.' — delivery runs automatically.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, SubOrder $subOrder)
    {
        $this->authorize('cancel', $subOrder);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->orderService->cancelSubOrder($subOrder, $validated['reason']);
            return redirect()->back()->with('success', 'Sub-order cancelled.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
