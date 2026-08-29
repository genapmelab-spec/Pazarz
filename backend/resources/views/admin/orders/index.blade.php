@extends('layouts.app')
@section('title', 'Orders')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Orders')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Order #</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Sellers</th>
                    <th class="px-6 py-3 font-medium">Grand Total</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $order->order_number }}</td>
                        <td class="px-6 py-4 text-sm">{{ $order->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->subOrders->pluck('store.name')->implode(', ') }}</td>
                        <td class="px-6 py-4 text-sm font-medium">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
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
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $orders->links() }}</div>
</div>
@endsection
