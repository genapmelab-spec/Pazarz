@extends('layouts.app')
@section('title', 'Products')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Products')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Product</th>
                    <th class="px-6 py-3 font-medium">Seller</th>
                    <th class="px-6 py-3 font-medium">Category</th>
                    <th class="px-6 py-3 font-medium">Price</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->store->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">Rp {{ number_format($product->base_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = ['active' => 'bg-green-100 text-green-800', 'draft' => 'bg-gray-100 text-gray-800', 'inactive' => 'bg-yellow-100 text-yellow-800'];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$product->status] ?? '' }}">{{ ucfirst($product->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $products->links() }}</div>
</div>
@endsection
