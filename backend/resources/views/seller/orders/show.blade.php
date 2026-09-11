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
            <div class="flex gap-3 flex-wrap items-center">
                @if($subOrder->status === 'pending')
                    @if(! in_array($subOrder->order->status, ['pending_payment', 'cancelled']))
                        <form method="POST" action="{{ route('seller.orders.confirm', $subOrder) }}">@csrf @method('PATCH')
                            <button class="bg-green-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-green-700">Confirm &amp; Ship</button>
                        </form>
                        <form method="POST" action="{{ route('seller.orders.cancel', $subOrder) }}" onsubmit="return confirm('Cancel this order?')">@csrf @method('PATCH')
                            <input type="hidden" name="reason" value="Out of stock">
                            <button class="bg-red-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-red-700">Cancel</button>
                        </form>
                    @else
                        <span class="text-sm text-gray-500">Menunggu pembayaran pembeli — tombol aksi aktif setelah pembayaran diterima.</span>
                    @endif
                @elseif(in_array($subOrder->status, ['confirmed', 'processing']))
                    <form method="POST" action="{{ route('seller.orders.confirm', $subOrder) }}">@csrf @method('PATCH')
                        <button class="bg-blue-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-blue-700">Kirim Sekarang</button>
                    </form>
                @else
                    <span class="text-sm text-gray-400">
                        @if(in_array($subOrder->status, ['confirmed', 'processing', 'shipped']))
                            Pengiriman berjalan otomatis — paket akan dinyatakan diterima tanpa perlu aksi pembeli.
                        @else
                            Tidak ada aksi untuk status ini.
                        @endif
                    </span>
                @endif
            </div>
        </div>

        <!-- Shipment Tracking -->
        @if($subOrder->shipment)
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold">Shipment</h3>
                    @php
                        $shipment = $subOrder->shipment;
                        $shipColors = ['in_transit'=>'bg-indigo-100 text-indigo-800','delivered'=>'bg-green-100 text-green-800'];
                    @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $shipColors[$shipment->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst(str_replace('_', ' ', $shipment->status)) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div><span class="text-gray-500">Courier:</span> {{ $shipment->courier }}</div>
                    <div><span class="text-gray-500">Tracking:</span> <span class="font-mono">{{ $shipment->tracking_number }}</span></div>
                    <div><span class="text-gray-500">Shipped:</span> {{ $shipment->shipped_at?->format('d M Y H:i') }}</div>
                    <div><span class="text-gray-500">Est. delivery:</span> {{ $shipment->estimated_delivery_at?->format('d M Y H:i') ?? '—' }}</div>
                </div>
                <div class="space-y-3">
                    @foreach($shipment->trackingEvents->sortByDesc('occurred_at') as $event)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium">{{ ucfirst(str_replace('_', ' ', $event->status)) }}</p>
                                <p class="text-xs text-gray-500">{{ $event->description }} — {{ $event->location }} · {{ $event->occurred_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="font-semibold mb-3">Status</h3>
            @php $colors = ['pending'=>'bg-yellow-100 text-yellow-800','confirmed'=>'bg-blue-100 text-blue-800','processing'=>'bg-indigo-100 text-indigo-800','shipped'=>'bg-green-100 text-green-800','completed'=>'bg-green-100 text-green-800','cancelled'=>'bg-red-100 text-red-800']; @endphp
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
