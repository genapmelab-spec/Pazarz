@extends('layouts.app')
@section('title', 'Add Product')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'products'])
@endsection
@section('header', 'Add Product')
@section('header-actions')
    <a href="{{ route('seller.products.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Products</a>
@endsection
@section('content')
<form method="POST" action="{{ route('seller.products.store') }}" class="max-w-3xl">
    @csrf

    <div class="space-y-8">
        <!-- Basic Info -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">Basic Information</h3>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">{{ old('description') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select name="category_id" id="category_id" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('category_id') border-red-500 @enderror">
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <optgroup label="{{ $category->name }}">
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @foreach($category->children as $child)
                                        <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
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
                            <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">Base Price (Rp) *</label>
                        <input type="number" name="base_price" id="base_price" value="{{ old('base_price') }}" min="0" step="100" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('base_price') border-red-500 @enderror">
                        @error('base_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="weight_grams" class="block text-sm font-medium text-gray-700 mb-1">Weight (grams) *</label>
                        <input type="number" name="weight_grams" id="weight_grams" value="{{ old('weight_grams') }}" min="0" required
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
                <input type="text" name="images[]" placeholder="https://example.com/image.jpg"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-2">
            </div>
            <button type="button" onclick="addImageField()" class="text-sm text-gray-600 hover:text-black mt-2">+ Add another image</button>
        </div>

        <!-- Variants -->
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <h3 class="text-base font-semibold mb-4">Variants</h3>
            <p class="text-sm text-gray-500 mb-3">Add variants if your product has different options (e.g., sizes, colors)</p>

            <div id="variants-container" class="space-y-4">
                <div class="variant-row border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-3 gap-4 mb-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
                            <input type="text" name="variants[0][sku]" placeholder="e.g. TSHIRT-BLK-M"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price Override (Rp)</label>
                            <input type="number" name="variants[0][price]" placeholder="Leave empty for base price" min="0" step="100"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock *</label>
                            <input type="number" name="variants[0][stock]" placeholder="0" min="0"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none" required>
                        </div>
                    </div>

                    @if($attributes->count())
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Attribute Values</label>
                        <div class="flex flex-wrap gap-4">
                            @foreach($attributes as $attr)
                            <div>
                                <label class="text-xs text-gray-500">{{ $attr->name }}</label>
                                <input type="text" name="variants[0][attributes][{{ $attr->name }}]"
                                    placeholder="{{ $attr->name }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <button type="button" onclick="this.closest('.variant-row').remove()" class="text-xs text-red-500 hover:text-red-700">Remove variant</button>
                </div>
            </div>

            <button type="button" onclick="addVariant()" class="mt-4 text-sm text-gray-600 hover:text-black">+ Add another variant</button>
        </div>

        <!-- Submit -->
        <div class="flex items-center gap-4">
            <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-800 transition">
                Create Product
            </button>
            <a href="{{ route('seller.products.index') }}" class="text-sm text-gray-600 hover:text-black">Cancel</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
let imageCount = 1;
function addImageField() {
    const container = document.getElementById('images-container');
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'images[]';
    input.placeholder = 'https://example.com/image.jpg';
    input.className = 'w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-2';
    container.appendChild(input);
    imageCount++;
}

let variantCount = 1;
function addVariant() {
    const container = document.getElementById('variants-container');
    const row = container.querySelector('.variant-row').cloneNode(true);
    row.querySelectorAll('input').forEach(input => {
        input.name = input.name.replace(/variants\[0\]/, `variants[${variantCount}]`);
        input.value = '';
    });
    container.appendChild(row);
    variantCount++;
}
</script>
@endpush
@endsection
