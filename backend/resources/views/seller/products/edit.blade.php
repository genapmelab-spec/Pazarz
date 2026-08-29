@extends('layouts.app')
@section('title', 'Edit Product')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'products'])
@endsection
@section('header', 'Edit Product')
@section('header-actions')
    <a href="{{ route('seller.products.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Products</a>
@endsection
@section('content')
<form method="POST" action="{{ route('seller.products.update', $product) }}" class="max-w-3xl">
    @csrf
    @method('PUT')

    <div class="space-y-8">
        <!-- Basic Info -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">Basic Information</h3>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">{{ old('description', $product->description) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category_id" id="category_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <optgroup label="{{ $category->name }}">
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id', $product->category_id) == $child->id ? 'selected' : '' }}>
                                            &nbsp;&nbsp;{{ $child->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                            <option value="draft" {{ old('status', $product->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ old('status', $product->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Base Price (Rp) *</label>
                        <input type="number" name="base_price" id="base_price" value="{{ old('base_price', $product->base_price) }}" min="0" step="100" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('base_price') border-red-500 @enderror">
                        @error('base_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="weight_grams" class="block text-sm font-medium text-gray-700 mb-1">Weight (grams) *</label>
                        <input type="number" name="weight_grams" id="weight_grams" value="{{ old('weight_grams', $product->weight_grams) }}" min="0" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('weight_grams') border-red-500 @enderror">
                        @error('weight_grams') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Images -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">Product Images</h3>
            <p class="text-sm text-gray-500 mb-3">Enter image URLs (comma-separated for multiple)</p>
            <div id="images-container">
                @if($product->images->count())
                    @foreach($product->images as $image)
                        <input type="text" name="images[]" value="{{ $image->url }}"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-2">
                    @endforeach
                @else
                    <input type="text" name="images[]" placeholder="https://example.com/image.jpg"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-2">
                @endif
            </div>
            <button type="button" onclick="addImageField()" class="text-sm text-gray-600 hover:text-black mt-2">+ Add another image</button>
        </div>

        <!-- Variants (read-only info — manage via Inventory page) -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">Current Variants</h3>
            @if($product->variants->count())
                <div class="space-y-3">
                    @foreach($product->variants as $variant)
                        <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center gap-4">
                                <span class="text-sm font-medium">{{ $variant->sku }}</span>
                                @if($variant->attributeValues->count())
                                    <span class="text-xs text-gray-500">
                                        @foreach($variant->attributeValues as $av)
                                            {{ $av->attribute?->name }}: {{ $av->value }}{{ $loop->last ? '' : ', ' }}
                                        @endforeach
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <span>Rp {{ number_format($variant->price ?? $product->base_price, 0, ',', '.') }}</span>
                                <span class="text-gray-500">Stock: {{ $variant->inventory->quantity ?? 0 }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-500 mt-3">To edit variant stock and attributes, use the <a href="{{ route('seller.inventory.index') }}" class="underline hover:text-black">Inventory</a> page.</p>
            @else
                <p class="text-sm text-gray-500">No variants — using base price only.</p>
            @endif
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-800 transition">
                Update Product
            </button>
            <a href="{{ route('seller.products.index') }}" class="text-sm text-gray-600 hover:text-black">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
function addImageField() {
    const container = document.getElementById('images-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'images[]';
    input.placeholder = 'https://example.com/image.jpg';
    input.className = 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-2';
    container.appendChild(input);
}
</script>
@endpush
@endsection
