@extends('layouts.app')
@section('title', 'Orders')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'orders'])
@endsection
@section('header', 'Orders')
@section('content')
<!-- Status Tabs -->
<div class="flex gap-2 mb-6">
    @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'shipped' => 'Shipped', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
        <a href="{{ route('seller.orders.index', array_filter(['status' => $value ?: null])) }}"
           class="px-4 py-2 rounded-full text-sm font-medium {{ request('status', '') === $value ? 'bg-black text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Order #</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Items</th>
                    <th class="px-6 py-3 font-medium">Total</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subOrders as $subOrder)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('seller.orders.show', $subOrder) }}" class="font-medium hover:underline">{{ $subOrder->order->order_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $subOrder->order->user->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $subOrder->items->count() }}</td>
                        <td class="px-6 py-4 text-sm font-medium">Rp {{ number_format($subOrder->subtotal, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @php $colors = ['pending'=>'bg-yellow-100 text-yellow-800','confirmed'=>'bg-blue-100 text-blue-800','shipped'=>'bg-green-100 text-green-800','completed'=>'bg-green-100 text-green-800','cancelled'=>'bg-red-100 text-red-800']; @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$subOrder->status] ?? '' }}">{{ ucfirst($subOrder->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($subOrder->status === 'pending')
                                <form method="POST" action="{{ route('seller.orders.confirm', $subOrder) }}" class="inline">@csrf @method('PATCH')
                                    <button class="text-green-600 hover:underline">Confirm</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $subOrders->withQueryString()->links() }}</div>
</div>
@endsection
