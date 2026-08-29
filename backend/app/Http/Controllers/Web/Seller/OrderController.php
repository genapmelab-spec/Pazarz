<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\SubOrder;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

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

    public function confirm(SubOrder $subOrder)
    {
        $this->authorize('confirm', $subOrder);

        try {
            $this->orderService->confirmSubOrder($subOrder);
            return redirect()->back()->with('success', 'Sub-order confirmed.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function ship(Request $request, SubOrder $subOrder)
    {
        $this->authorize('ship', $subOrder);

        $validated = $request->validate([
            'courier' => 'required|string|max:50',
            'tracking_number' => 'required|string|max:100',
        ]);

        try {
            $this->orderService->shipSubOrder($subOrder, $validated['courier'], $validated['tracking_number']);
            return redirect()->back()->with('success', 'Sub-order shipped.');
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
