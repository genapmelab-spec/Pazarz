@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-900">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        Dashboard
    </a>
    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        Users
    </a>
    <a href="{{ route('admin.sellers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Sellers
    </a>
    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        Products
    </a>
    <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        Categories
    </a>
    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        Orders
    </a>
    <a href="{{ route('admin.disputes.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Disputes
    </a>
    <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Audit Logs
    </a>
    <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        Settings
    </a>
@endsection

@section('header', 'Platform Overview')

@section('content')
<!-- Metric Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Total GMV</p>
        <p class="text-xl font-bold">Rp {{ number_format($totalGMV, 0, ',', '.') }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Today's Orders</p>
        <p class="text-xl font-bold">{{ $todayOrders }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Pending Sellers</p>
        <p class="text-xl font-bold text-yellow-600">{{ $pendingSellers }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Total Users</p>
        <p class="text-xl font-bold">{{ $totalUsers }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Total Products</p>
        <p class="text-xl font-bold">{{ $totalProducts }}</p>
    </div>
    <div class="bg-white rounded-2xl p-5 border border-gray-100">
        <p class="text-xs text-gray-500 mb-1">Open Disputes</p>
        <p class="text-xl font-bold text-red-600">{{ $openDisputes }}</p>
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
                    <th class="px-6 py-3 font-medium">Sellers</th>
                    <th class="px-6 py-3 font-medium">Grand Total</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-medium hover:underline">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $order->user->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $order->subOrders->pluck('store.name')->implode(', ') }}</td>
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
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">No orders yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
