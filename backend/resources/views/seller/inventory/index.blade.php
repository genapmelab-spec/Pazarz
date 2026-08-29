@extends('layouts.app')
@section('title', 'Inventory')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'inventory'])
@endsection
@section('header', 'Inventory Management')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="p-4 border-b border-gray-100 flex gap-2">
        <a href="{{ route('seller.inventory.index') }}" class="px-3 py-1.5 rounded-full text-sm {{ !request('low_stock') ? 'bg-black text-white' : 'bg-gray-100' }}">All</a>
        <a href="{{ route('seller.inventory.index', ['low_stock' => 1]) }}" class="px-3 py-1.5 rounded-full text-sm {{ request('low_stock') ? 'bg-red-100 text-red-800' : 'bg-gray-100' }}">Low Stock</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Product</th>
                    <th class="px-6 py-3 font-medium">Variant</th>
                    <th class="px-6 py-3 font-medium">SKU</th>
                    <th class="px-6 py-3 font-medium">Stock</th>
                    <th class="px-6 py-3 font-medium">Reserved</th>
                    <th class="px-6 py-3 font-medium">Available</th>
                    <th class="px-6 py-3 font-medium">Threshold</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($variants as $inv)
                    <tr class="border-b border-gray-50 hover:bg-gray-50 {{ $inv->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium">{{ $inv->variant->product->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ $inv->variant->label }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $inv->variant->sku }}</td>
                        <td class="px-6 py-4 text-sm">{{ $inv->quantity }}</td>
                        <td class="px-6 py-4 text-sm">{{ $inv->reserved_quantity }}</td>
                        <td class="px-6 py-4 text-sm {{ $inv->isLowStock() ? 'text-red-600 font-medium' : '' }}">{{ $inv->available_stock }}</td>
                        <td class="px-6 py-4 text-sm">{{ $inv->low_stock_threshold }}</td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('seller.inventory.update', $inv) }}" class="flex gap-2 items-center">@csrf @method('PATCH')
                                <input type="number" name="quantity" value="{{ $inv->quantity }}" min="0" class="w-20 px-2 py-1 border rounded text-sm">
                                <button class="text-blue-600 text-sm hover:underline">Save</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-12 text-center text-gray-500 text-sm">No inventory items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $variants->withQueryString()->links() }}</div>
</div>
@endsection
