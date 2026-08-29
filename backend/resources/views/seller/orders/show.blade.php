@extends('layouts.app')
@section('title', 'Order Detail')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'orders'])
@endsection
@section('header', 'Order #' . $subOrder->order->order_number)
@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Order Info -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-4">Order Items</h3>
            @foreach($subOrder->items as $item)
                <div class="flex items-center gap-4 py-3 border-b border-gray-50 last:border-0">
                    <div class="w-12 h-12 bg-gray-100 rounded-lg flex-shrink-0"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $item->product_name_snapshot }}</p>
                        <p class="text-xs text-gray-500">{{ $item->variant_label_snapshot }} × {{ $item->quantity }}</p>
                    </div>
                    <p class="text-sm font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                </div>
            @endforeach
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-4">Actions</h3>
            <div class="flex gap-3 flex-wrap">
                @if($subOrder->status === 'pending')
                    <form method="POST" action="{{ route('seller.orders.confirm', $subOrder) }}">@csrf @method('PATCH')
                        <button class="bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-green-700">Confirm Order</button>
                    </form>
                    <form method="POST" action="{{ route('seller.orders.cancel', $subOrder) }}" onsubmit="return confirm('Cancel this order?')">@csrf @method('PATCH')
                        <input type="hidden" name="reason" value="Out of stock">
                        <button class="bg-red-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-red-700">Cancel</button>
                    </form>
                @endif
                @if($subOrder->status === 'confirmed' || $subOrder->status === 'processing')
                    <form method="POST" action="{{ route('seller.orders.ship', $subOrder) }}" class="flex gap-2">@csrf @method('PATCH')
                        <input type="text" name="courier" placeholder="Courier" required class="px-3 py-2 border rounded-lg text-sm">
                        <input type="text" name="tracking_number" placeholder="Tracking #" required class="px-3 py-2 border rounded-lg text-sm">
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-700">Ship</button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-3">Status</h3>
            @php $colors = ['pending'=>'bg-yellow-100 text-yellow-800','confirmed'=>'bg-blue-100 text-blue-800','shipped'=>'bg-green-100 text-green-800','completed'=>'bg-green-100 text-green-800','cancelled'=>'bg-red-100 text-red-800']; @endphp
            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $colors[$subOrder->status] ?? '' }}">{{ ucfirst($subOrder->status) }}</span>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-3">Customer</h3>
            <p class="text-sm">{{ $subOrder->order->user->name }}</p>
            <p class="text-sm text-gray-500">{{ $subOrder->order->user->email }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-3">Shipping Address</h3>
            <p class="text-sm">{{ $subOrder->order->shippingAddress->full_address }}</p>
            <p class="text-sm text-gray-500">{{ $subOrder->order->shippingAddress->city }}, {{ $subOrder->order->shippingAddress->province }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-3">Summary</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span>Subtotal</span><span>Rp {{ number_format($subOrder->subtotal, 0, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>Shipping</span><span>Rp {{ number_format($subOrder->shipping_cost, 0, ',', '.') }}</span></div>
                <hr>
                <div class="flex justify-between font-semibold"><span>Total</span><span>Rp {{ number_format($subOrder->subtotal + $subOrder->shipping_cost, 0, ',', '.') }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
