@extends('layouts.app')
@section('title', 'Add Promotion')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'promotions'])
@endsection
@section('header', 'Add Promotion')
@section('header-actions')
    <a href="{{ route('seller.promotions.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Promotions</a>
@endsection
@section('content')
<form method="POST" action="{{ route('seller.promotions.store') }}" class="max-w-xl">
    @csrf
    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
            <select name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                <option value="">Select product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }} — Rp {{ number_format($product->base_price, 0, ',', '.') }}</option>
                @endforeach
            </select>
            @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount (%)</label>
                <input type="number" name="discount_percent" value="{{ old('discount_percent') }}" min="1" max="90"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Discount (Rp)</label>
                <input type="number" name="discount_amount" value="{{ old('discount_amount') }}" min="0" step="1000"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date *</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>
        </div>
        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-800 transition">Create Promotion</button>
            <a href="{{ route('seller.promotions.index') }}" class="text-sm text-gray-600 hover:text-black">Cancel</a>
        </div>
    </div>
</form>
@endsection
