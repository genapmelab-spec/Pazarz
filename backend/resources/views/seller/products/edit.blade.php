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
<form method="POST" action="{{ route('seller.products.update', $product) }}" class="max-w-3xl" enctype="multipart/form-data" novalidate>
    @csrf
    @method('PUT')

    <div class="space-y-8">
        {{-- Validation Banner --}}
        <div id="formBanner" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-red-700">Belum bisa dipublikasikan</p>
                    <ul id="bannerList" class="text-sm text-red-600 mt-1 list-disc list-inside space-y-0.5"></ul>
                </div>
            </div>
        </div>
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
            <p class="text-sm text-gray-500 mb-3">Upload images from your device or paste image URLs. The first image is the main image.</p>
            <div id="images-container" class="space-y-3">
                @forelse($product->images as $image)
                    <div class="image-row flex flex-col sm:flex-row gap-2">
                        <label class="flex-1 flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-sm text-gray-500 overflow-hidden">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="file-label truncate">Ganti dari perangkat</span>
                            <input type="file" accept="image/*" name="images[{{ $loop->index }}][file]" class="hidden file-input" onchange="previewImageFile(this)">
                        </label>
                        <input type="text" name="images[{{ $loop->index }}][url]" value="{{ $image->url }}"
                            class="image-input flex-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none"
                            oninput="previewImage(this)">
                    </div>
                @empty
                    <div class="image-row flex flex-col sm:flex-row gap-2">
                        <label class="flex-1 flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-sm text-gray-500 overflow-hidden">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="file-label truncate">Upload dari perangkat</span>
                            <input type="file" accept="image/*" name="images[0][file]" class="hidden file-input" onchange="previewImageFile(this)">
                        </label>
                        <input type="text" name="images[0][url]" placeholder="atau tempel URL gambar https://..."
                            class="image-input flex-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none"
                            oninput="previewImage(this)">
                    </div>
                @endforelse
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
    const index = container.querySelectorAll('.image-row').length;
    const row = document.createElement('div');
    row.className = 'image-row flex flex-col sm:flex-row gap-2';
    row.innerHTML = `
        <label class="flex-1 flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-sm text-gray-500 overflow-hidden">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
            <span class="file-label truncate">Upload dari perangkat</span>
            <input type="file" accept="image/*" name="images[${index}][file]" class="hidden file-input" onchange="previewImageFile(this)">
        </label>
        <input type="text" name="images[${index}][url]" placeholder="atau tempel URL gambar https://..."
            class="image-input flex-1 w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none"
            oninput="previewImage(this)">
    `;
    container.appendChild(row);
}

function previewImage(input) {
    const preview = input.closest('.image-row').querySelector('.file-label');
    if (preview && !input.value.trim()) {
        preview.textContent = 'Ganti dari perangkat';
    }
}

function previewImageFile(input) {
    const label = input.closest('.image-row').querySelector('.file-label');
    if (label) label.textContent = input.files && input.files[0] ? input.files[0].name : 'Ganti dari perangkat';
}

// =================== Submit Validation ===================
function showBanner(missingItems) {
    const banner = document.getElementById('formBanner');
    const list = document.getElementById('bannerList');
    list.innerHTML = '';
    missingItems.forEach(item => {
        const li = document.createElement('li');
        li.textContent = item;
        list.appendChild(li);
    });
    banner.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.querySelector('form[action*="products/"]').addEventListener('submit', function (e) {
    const status = document.getElementById('status').value;
    const missing = [];

    if (!document.getElementById('name').value.trim()) missing.push('Nama produk wajib diisi.');
    if (!document.getElementById('category_id').value) missing.push('Pilih kategori produk.');

    if (status === 'active') {
        const price = document.getElementById('base_price').value;
        const weight = document.getElementById('weight_grams').value;
        if (!price || parseFloat(price) <= 0) missing.push('Harga produk wajib diisi dan harus lebih dari 0.');
        if (!weight || parseInt(weight) <= 0) missing.push('Berat produk wajib diisi dan harus lebih dari 0.');

        let hasImage = false;
        document.querySelectorAll('.image-row').forEach(row => {
            const file = row.querySelector('.file-input');
            const url = row.querySelector('.image-input');
            if ((file && file.files && file.files[0]) || (url && url.value.trim())) hasImage = true;
        });
        if (!hasImage) missing.push('Minimal satu gambar produk wajib diisi (upload file atau tempel URL).');
    }

    if (missing.length) {
        e.preventDefault();
        showBanner(missing);
    }
});
</script>
@endpush
@endsection
