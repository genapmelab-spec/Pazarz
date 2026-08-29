@extends('layouts.app')
@section('title', 'Product Details')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Product: ' . $product->name)
@section('header-actions')
    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Products</a>
@endsection
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Product Information</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Name</p>
                <p class="font-medium">{{ $product->name }}</p>
            </div>
            <div>
                <p class="text-gray-500">Category</p>
                <p class="font-medium">{{ $product->category->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Seller / Store</p>
                <p class="font-medium">{{ $product->store->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Base Price</p>
                <p class="font-medium">Rp {{ number_format($product->base_price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                @php
                    $statusColors = ['active' => 'bg-green-100 text-green-800', 'draft' => 'bg-gray-100 text-gray-800', 'inactive' => 'bg-yellow-100 text-yellow-800'];
                @endphp
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$product->status] ?? '' }}">{{ ucfirst($product->status) }}</span>
            </div>
            <div>
                <p class="text-gray-500">Weight</p>
                <p class="font-medium">{{ $product->weight_grams }}g</p>
            </div>
            @if($product->description)
            <div class="col-span-2">
                <p class="text-gray-500">Description</p>
                <p class="font-medium">{{ $product->description }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Actions</h3>
        <div class="flex gap-3">
            @if($product->status === 'active')
                <form method="POST" action="{{ route('admin.products.deactivate', $product) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-yellow-700"
                        onclick="return confirm('Deactivate this product?')">Deactivate</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.products.activate', $product) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Activate</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
