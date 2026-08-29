@extends('layouts.app')
@section('title', 'Order Details')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Order: ' . $order->order_number)
@section('header-actions')
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Orders</a>
@endsection
@section('content')
<div class="max-w-4xl space-y-6">
    <!-- Order Info -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Order Information</h3>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Order #</p>
                <p class="font-medium">{{ $order->order_number }}</p>
            </div>
            <div>
                <p class="text-gray-500">Customer</p>
                <p class="font-medium">{{ $order->user->name }}</p>
            </div>
            <div>
                <p class="text-gray-500">Date</p>
                <p class="font-medium">{{ $order->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                @php
                    $statusColors = [
                        'pending_payment' => 'bg-yellow-100 text-yellow-800',
                        'paid' => 'bg-blue-100 text-blue-800',
                        'processing' => 'bg-blue-100 text-blue-800',
                        'completed' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                @endphp
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                </span>
            </div>
            <div>
                <p class="text-gray-500">Subtotal</p>
                <p class="font-medium">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Shipping</p>
                <p class="font-medium">Rp {{ number_format($order->shipping_total, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Discount</p>
                <p class="font-medium">-Rp {{ number_format($order->discount_total, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Grand Total</p>
                <p class="font-medium text-lg">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Sub-orders -->
    @foreach($order->subOrders as $subOrder)
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">{{ $subOrder->store->name ?? 'Store' }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-left text-gray-500">
                            <th class="pb-2 font-medium">Item</th>
                            <th class="pb-2 font-medium">Qty</th>
                            <th class="pb-2 font-medium">Price</th>
                            <th class="pb-2 font-medium">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subOrder->items as $item)
                            <tr class="border-b border-gray-50">
                                <td class="py-2">{{ $item->product_name_snapshot }}</td>
                                <td class="py-2">{{ $item->quantity }}</td>
                                <td class="py-2">Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</td>
                                <td class="py-2 font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-100 text-sm">
                <span class="text-gray-500">Sub-order Status: <span class="font-medium text-gray-900">{{ ucfirst($subOrder->status) }}</span></span>
                <span class="font-medium">Subtotal: Rp {{ number_format($subOrder->subtotal, 0, ',', '.') }}</span>
            </div>
        </div>
    @endforeach
</div>
@endsection
