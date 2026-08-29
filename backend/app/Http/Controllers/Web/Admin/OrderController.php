<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['user', 'subOrders.store', 'payment'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('order_number', 'LIKE', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'shippingAddress',
            'subOrders.store',
            'subOrders.items.variant.product',
            'subOrders.shipment.trackingEvents',
            'payment',
        ]);

        return view('admin.orders.show', compact('order'));
    }
}
