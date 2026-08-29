@extends('layouts.app')
@section('title', 'Products')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'products'])
@endsection
@section('header', 'Products')
@section('header-actions')
    <a href="{{ route('seller.products.create') }}" class="bg-black text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800">+ Add Product</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Product</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Price</th>
                    <th class="px-6 py-3 font-medium">Stock</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                @if($product->primaryImage)
                                    <img src="{{ $product->primaryImage->url }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded-lg"></div>
                                @endif
                                <span class="text-sm font-medium">{{ $product->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category->name }}</td>
                        <td class="px-6 py-4 text-sm">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $product->variants->sum(fn($v) => $v->inventory->quantity ?? 0) }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = ['active' => 'bg-green-100 text-green-800', 'draft' => 'bg-gray-100 text-gray-800', 'inactive' => 'bg-yellow-100 text-yellow-800'];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$product->status] ?? '' }}">{{ ucfirst($product->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('seller.products.edit', $product) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No products yet. <a href="{{ route('seller.products.create') }}" class="text-black font-medium hover:underline">Add your first product</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $products->links() }}</div>
</div>
@endsection
