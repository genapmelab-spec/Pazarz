@extends('layouts.app')

@section('title', 'Seller Dashboard')

@section('sidebar')
    <a href="{{ route('seller.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('seller.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Products
    </a>
    <a href="{{ route('seller.inventory.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
        Inventory
    </a>
    <a href="{{ route('seller.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Orders
    </a>
    <a href="{{ route('seller.reviews.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
        Reviews
    </a>
    <a href="{{ route('seller.promotions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
        Promotions
    </a>
    <a href="{{ route('seller.settings.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Settings
    </a>
@endsection

@section('header', 'Dashboard')

@section('content')
<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <p class="text-sm text-gray-500 mb-1">Today's Revenue</p>
        <p class="text-2xl font-bold">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <p class="text-sm text-gray-500 mb-1">This Month Revenue</p>
        <p class="text-2xl font-bold">Rp {{ number_format($monthRevenue, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <p class="text-sm text-gray-500 mb-1">New Orders</p>
        <p class="text-2xl font-bold">{{ $newOrders }}</p>
    </div>
    <div class="bg-white rounded-2xl p-6 border border-gray-100">
        <p class="text-sm text-gray-500 mb-1">Low Stock Products</p>
        <p class="text-2xl font-bold">{{ $lowStockProducts }}</p>
    </div>
</div>

<!-- Recent Orders -->
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-semibold">Recent Orders</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Order #</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Items</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $subOrder)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('seller.orders.show', $subOrder) }}" class="font-medium hover:underline">
                                {{ $subOrder->order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $subOrder->order->user->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $subOrder->items->count() }}</td>
                        <td class="px-6 py-4 text-sm font-medium">Rp {{ number_format($subOrder->subtotal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'shipped' => 'bg-green-100 text-green-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$subOrder->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($subOrder->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">
                            No orders yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
