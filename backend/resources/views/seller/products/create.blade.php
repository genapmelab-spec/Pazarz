@extends('layouts.app')
@section('title', 'Tambah Produk Baru')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'products'])
@endsection
@section('header', 'Tambah Produk Baru')
@section('header-actions')
    <a href="{{ route('seller.products.index') }}" class="text-sm text-gray-500 hover:text-gray-800 transition">
        ← Kembali ke Produk
    </a>
@endsection
@section('content')
<form method="POST" action="{{ route('seller.products.store') }}" id="productForm" enctype="multipart/form-data" novalidate>
    @csrf

    <div class="max-w-4xl mx-auto">
        {{-- Validation Banner --}}
        <div id="formBanner" class="hidden mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
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

        {{-- Always-visible Save Draft --}}
        <div class="flex justify-end mb-6">
            <button type="submit" onclick="document.getElementById('status').value='draft'"
                class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-black transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2zM17 21v-8H7v8M7 3v5h8" /></svg>
                Simpan Draft
            </button>
        </div>

        {{-- Step Progress Bar --}}
        <div class="flex items-center justify-center mb-10" id="progressBar">
            <div class="flex items-center gap-0">
                @foreach(['Informasi Dasar', 'Gambar Produk', 'Varian & Stok', 'Tinjauan'] as $i => $step)
                    <div class="flex items-center">
                        <div class="step-indicator flex items-center gap-2 cursor-pointer" data-step="{{ $i + 1 }}">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200
                                {{ $i === 0 ? 'bg-black text-white' : 'bg-gray-200 text-gray-500' }}" id="stepCircle{{ $i + 1 }}">
                                {{ $i + 1 }}
                            </div>
                            <span class="text-xs font-medium hidden sm:inline transition-colors {{ $i === 0 ? 'text-black' : 'text-gray-400' }}" id="stepLabel{{ $i + 1 }}">
                                {{ $step }}
                            </span>
                        </div>
                        @if($i < 3)
                            <div class="w-8 sm:w-16 h-[2px] mx-2 bg-gray-200 transition-colors" id="stepLine{{ $i + 1 }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ========== STEP 1: Basic Info ========== --}}
        <div class="step-content" id="step1">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 shadow-sm">
                <h3 class="text-lg font-semibold mb-1">Informasi Dasar</h3>
                <p class="text-sm text-gray-500 mb-6">Ceritakan tentang produkmu kepada pembeli.</p>

                <div class="space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Produk *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="Contoh: Vintage Leather Jacket - Black"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition @error('name') border-red-500 @enderror">
                        @error('name') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi Produk</label>
                        <textarea name="description" id="description" rows="5"
                            placeholder="Jelaskan bahan, ukuran, kondisi, dan detail penting lainnya..."
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition resize-none">{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">Tip: Deskripsi yang jelas meningkatkan kepercayaan pembeli.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori *</label>
                            <select name="category_id" id="category_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition @error('category_id') border-red-500 @enderror">
                                <option value="">Pilih kategori...</option>
                                @foreach($categories as $category)
                                    <optgroup label="{{ $category->name }}">
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @foreach($category->children as $child)
                                            <option value="{{ $child->id }}" {{ old('category_id') == $child->id ? 'selected' : '' }}>
                                                &nbsp;&nbsp;└ {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('category_id') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1.5">Status Publikasi</label>
                            <select name="status" id="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition">
                                <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>📝 Draft — Tidak ditampilkan ke pembeli</option>
                                <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>🟢 Aktif — Ditampilkan ke pembeli</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="base_price" class="block text-sm font-semibold text-gray-700 mb-1.5">Harga (Rp) *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-3 text-sm text-gray-400">Rp</span>
                                <input type="number" name="base_price" id="base_price" value="{{ old('base_price') }}" min="0" step="100" required
                                    placeholder="0"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition @error('base_price') border-red-500 @enderror">
                            </div>
                            @error('base_price') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="weight_grams" class="block text-sm font-semibold text-gray-700 mb-1.5">Berat (gram) *</label>
                            <div class="relative">
                                <input type="number" name="weight_grams" id="weight_grams" value="{{ old('weight_grams') }}" min="0" required
                                    placeholder="0"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition @error('weight_grams') border-red-500 @enderror">
                                <span class="absolute right-4 top-3 text-sm text-gray-400">gram</span>
                            </div>
                            @error('weight_grams') <p class="text-red-500 text-xs mt-1.5">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" onclick="goToStep(2)" class="bg-black text-white px-8 py-3 rounded-full text-sm font-semibold hover:bg-gray-800 active:scale-[0.98] transition-all">
                    Selanjutnya →
                </button>
            </div>
        </div>

        {{-- ========== STEP 2: Images ========== --}}
        <div class="step-content hidden" id="step2">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 shadow-sm">
                <h3 class="text-lg font-semibold mb-1">Gambar Produk</h3>
                <p class="text-sm text-gray-500 mb-6">Upload gambar dari perangkatmu atau tempel URL gambar. Gambar pertama akan menjadi gambar utama.</p>

                <div id="imagesContainer" class="space-y-4">
                    <div class="image-row flex gap-3 items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="w-6 h-6 rounded-full bg-black text-white text-xs font-bold flex items-center justify-center">1</span>
                                <span class="text-xs font-medium text-gray-600">Gambar Utama</span>
                            </div>
                            <div class="flex flex-col sm:flex-row gap-2">
                                <label class="flex-1 flex items-center gap-2 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-sm text-gray-500 overflow-hidden">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span class="file-label truncate">Upload dari perangkat</span>
                                    <input type="file" accept="image/*" name="images[0][file]" class="hidden file-input" onchange="previewImageFile(this)">
                                </label>
                                <input type="text" name="images[0][url]" placeholder="atau tempel URL gambar https://..."
                                    class="image-input flex-1 w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition"
                                    oninput="previewImage(this)">
                            </div>
                        </div>
                        <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden mt-5">
                            <img src="" alt="" class="image-preview w-full h-full object-cover rounded-xl hidden">
                            <svg class="w-6 h-6 text-gray-300 preview-placeholder" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addImageField()" class="mt-4 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-black font-medium transition">
                    <span class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center text-xs">+</span>
                    Tambah Gambar Lainnya
                </button>
                <p class="text-xs text-gray-400 mt-2">Anda bisa menambahkan beberapa gambar. Gambar pertama adalah gambar utama.</p>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="goToStep(1)" class="text-sm text-gray-600 hover:text-black font-medium px-6 py-3 transition">
                    ← Kembali
                </button>
                <button type="button" onclick="goToStep(3)" class="bg-black text-white px-8 py-3 rounded-full text-sm font-semibold hover:bg-gray-800 active:scale-[0.98] transition-all">
                    Selanjutnya →
                </button>
            </div>
        </div>

        {{-- ========== STEP 3: Variants ========== --}}
        <div class="step-content hidden" id="step3">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 shadow-sm">
                <h3 class="text-lg font-semibold mb-1">Varian & Stok</h3>
                <p class="text-sm text-gray-500 mb-6">Tambahkan varian jika produkmu punya beberapa opsi (warna, ukuran, dll). Kosongkan jika hanya ada satu jenis.</p>

                <div id="variantsContainer" class="space-y-4">
                    <div class="variant-card border border-gray-200 rounded-xl p-5 relative">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-semibold text-gray-700">Varian #1</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">SKU *</label>
                                <input type="text" name="variants[0][sku]" placeholder="Contoh: JL-BLACK-M"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none transition" required>
                                <p class="text-xs text-gray-400 mt-1">Kode unik untuk varian ini</p>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Harga Override (Rp)</label>
                                <input type="number" name="variants[0][price]" placeholder="Kosongkan = harga dasar" min="0" step="100"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none transition">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Stok *</label>
                                <input type="number" name="variants[0][stock]" placeholder="0" min="0"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none transition" required>
                            </div>
                        </div>

                        @if($attributes->count())
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-gray-600 mb-2">Atribut</label>
                            <div class="flex flex-wrap gap-3">
                                @foreach($attributes as $attr)
                                <div>
                                    <label class="text-xs text-gray-400">{{ $attr->name }}</label>
                                    <input type="text" name="variants[0][attributes][{{ $attr->id }}]"
                                        placeholder="{{ $attr->name }}"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-black focus:border-transparent outline-none transition mt-0.5">
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <button type="button" onclick="this.closest('.variant-card').remove(); renumberVariants();" class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                            🗑 Hapus varian ini
                        </button>
                    </div>
                </div>

                <button type="button" onclick="addVariant()" class="mt-4 inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-black font-medium transition">
                    <span class="w-5 h-5 rounded-full border border-gray-300 flex items-center justify-center text-xs">+</span>
                    Tambah Varian Lainnya
                </button>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="goToStep(2)" class="text-sm text-gray-600 hover:text-black font-medium px-6 py-3 transition">
                    ← Kembali
                </button>
                <button type="button" onclick="goToStep(4)" class="bg-black text-white px-8 py-3 rounded-full text-sm font-semibold hover:bg-gray-800 active:scale-[0.98] transition-all">
                    Selanjutnya →
                </button>
            </div>
        </div>

        {{-- ========== STEP 4: Review ========== --}}
        <div class="step-content hidden" id="step4">
            <div class="bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 shadow-sm">
                <h3 class="text-lg font-semibold mb-1">Tinjauan Produk</h3>
                <p class="text-sm text-gray-500 mb-6">Periksa kembali data produkmu sebelum dipublikasikan.</p>

                <div class="space-y-4" id="reviewSection">
                    <div class="flex items-start gap-4 p-4 bg-gray-50 rounded-xl">
                        <div class="w-20 h-20 rounded-xl bg-gray-200 overflow-hidden flex-shrink-0" id="reviewImage">
                            <svg class="w-full h-full text-gray-300 p-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold" id="reviewName">-</p>
                            <p class="text-xs text-gray-500 mt-0.5" id="reviewCategory">-</p>
                            <p class="text-sm font-bold mt-1" id="reviewPrice">-</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 mb-1">Deskripsi</p>
                            <p class="text-sm" id="reviewDescription">-</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 mb-1">Berat</p>
                            <p class="text-sm" id="reviewWeight">-</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 mb-1">Status</p>
                            <p class="text-sm" id="reviewStatus">-</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-xl">
                            <p class="text-xs text-gray-500 mb-1">Varian</p>
                            <p class="text-sm" id="reviewVariants">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" onclick="goToStep(3)" class="text-sm text-gray-600 hover:text-black font-medium px-6 py-3 transition">
                    ← Kembali
                </button>
                <div class="flex items-center gap-3">
                    <button type="submit" onclick="document.getElementById('status').value='draft'" class="border border-gray-300 text-gray-700 px-6 py-3 rounded-full text-sm font-medium hover:bg-gray-50 active:scale-[0.98] transition-all">
                        Simpan Draft
                    </button>
                    <button type="submit" onclick="document.getElementById('status').value='active'" class="bg-black text-white px-8 py-3 rounded-full text-sm font-semibold hover:bg-gray-800 active:scale-[0.98] transition-all">
                        🚀 Publikasikan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
// =================== Step Navigation ===================
let currentStep = 1;

function goToStep(step) {
    // Validate current step before moving forward
    if (step > currentStep) {
        if (!validateStep(currentStep)) return;
    }

    // Update step visibility
    document.querySelectorAll('.step-content').forEach(el => el.classList.add('hidden'));
    document.getElementById('step' + step).classList.remove('hidden');

    // Update progress indicators
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById('stepCircle' + i);
        const label = document.getElementById('stepLabel' + i);
        const line = document.getElementById('stepLine' + i);

        if (i < step) {
            // Completed
            circle.className = 'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200 bg-green-500 text-white';
            circle.innerHTML = '✓';
            if (label) label.className = 'text-xs font-medium hidden sm:inline transition-colors text-green-600';
            if (line) line.className = 'w-8 sm:w-16 h-[2px] mx-2 bg-green-400 transition-colors';
        } else if (i === step) {
            // Current
            circle.className = 'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200 bg-black text-white';
            circle.innerHTML = i;
            if (label) label.className = 'text-xs font-medium hidden sm:inline transition-colors text-black';
            if (line) line.className = 'w-8 sm:w-16 h-[2px] mx-2 bg-gray-200 transition-colors';
        } else {
            // Pending
            circle.className = 'w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-200 bg-gray-200 text-gray-500';
            circle.innerHTML = i;
            if (label) label.className = 'text-xs font-medium hidden sm:inline transition-colors text-gray-400';
            if (line) line.className = 'w-8 sm:w-16 h-[2px] mx-2 bg-gray-200 transition-colors';
        }
    }

    currentStep = step;

    // Update review on step 4
    if (step === 4) updateReview();

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    if (step === 1) {
        const name = document.getElementById('name').value.trim();
        const category = document.getElementById('category_id').value;

        if (!name) { alert('Nama produk wajib diisi.'); document.getElementById('name').focus(); return false; }
        if (!category) { alert('Pilih kategori produk.'); document.getElementById('category_id').focus(); return false; }
    }
    return true;
}

// Make step indicators clickable
document.querySelectorAll('.step-indicator').forEach(el => {
    el.addEventListener('click', () => {
        const targetStep = parseInt(el.dataset.step);
        if (targetStep <= currentStep || targetStep === currentStep + 1) {
            goToStep(targetStep);
        }
    });
});

// =================== Image Management ===================
function addImageField() {
    const container = document.getElementById('imagesContainer');
    const index = container.querySelectorAll('.image-row').length;
    const row = document.createElement('div');
    row.className = 'image-row flex gap-3 items-start';
    row.innerHTML = `
        <div class="flex-1">
            <div class="flex items-center gap-2 mb-1.5">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 text-xs font-bold flex items-center justify-center">${index + 1}</span>
                <span class="text-xs font-medium text-gray-500">Gambar Tambahan</span>
                <button type="button" onclick="this.closest('.image-row').remove(); renumberImages();" class="ml-auto text-xs text-red-500 hover:text-red-700 font-medium">✕ Hapus</button>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <label class="flex-1 flex items-center gap-2 px-4 py-3 border border-gray-300 rounded-xl bg-gray-50 hover:bg-gray-100 cursor-pointer transition text-sm text-gray-500 overflow-hidden">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="file-label truncate">Upload dari perangkat</span>
                    <input type="file" accept="image/*" name="images[${index}][file]" class="hidden file-input" onchange="previewImageFile(this)">
                </label>
                <input type="text" name="images[${index}][url]" placeholder="atau tempel URL gambar https://..."
                    class="image-input flex-1 w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-black focus:border-transparent outline-none text-sm transition"
                    oninput="previewImage(this)">
            </div>
        </div>
        <div class="w-20 h-20 rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 flex items-center justify-center overflow-hidden mt-5">
            <img src="" alt="" class="image-preview w-full h-full object-cover rounded-xl hidden">
            <svg class="w-6 h-6 text-gray-300 preview-placeholder" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    `;
    container.appendChild(row);
}

function renumberImages() {
    const rows = document.querySelectorAll('.image-row');
    rows.forEach((row, i) => {
        const badge = row.querySelector('.rounded-full.bg-gray-200, .rounded-full.bg-black');
        if (badge && badge.textContent.trim() !== '✓') {
            badge.textContent = i + 1;
        }
        row.querySelectorAll('[name^="images["]').forEach(input => {
            input.name = input.name.replace(/^images\[\d+\]/, `images[${i}]`);
        });
    });
}

function showPreview(row, src) {
    const preview = row.querySelector('.image-preview');
    const placeholder = row.querySelector('.preview-placeholder');
    if (src) {
        preview.src = src;
        preview.onload = () => {
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        preview.onerror = () => {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        };
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }
}

function previewImage(input) {
    showPreview(input.closest('.image-row'), input.value.trim());
}

function previewImageFile(input) {
    const row = input.closest('.image-row');
    const label = row.querySelector('.file-label');
    if (label) label.textContent = input.files && input.files[0] ? input.files[0].name : 'Upload dari perangkat';
    showPreview(row, input.files && input.files[0] ? URL.createObjectURL(input.files[0]) : '');
}

// =================== Variant Management ===================
let variantCount = 1;

function addVariant() {
    variantCount++;
    const container = document.getElementById('variantsContainer');
    const firstCard = container.querySelector('.variant-card');
    const newCard = firstCard.cloneNode(true);

    newCard.querySelectorAll('input').forEach(input => {
        input.name = input.name.replace(/variants\[\d+\]/, `variants[${variantCount - 1}]`);
        input.value = '';
    });

    // Update label
    newCard.querySelector('.text-sm.font-semibold').textContent = `Varian #${variantCount}`;

    // Remove attribute section if original had one (clone it properly)
    const attrsDiv = newCard.querySelector('.mb-3:last-of-type');
    if (attrsDiv) {
        attrsDiv.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/variants\[\d+\]/, `variants[${variantCount - 1}]`);
        });
    }

    container.appendChild(newCard);
}

function renumberVariants() {
    const cards = document.querySelectorAll('.variant-card');
    cards.forEach((card, i) => {
        card.querySelector('.text-sm.font-semibold').textContent = `Varian #${i + 1}`;
        card.querySelectorAll('input').forEach(input => {
            input.name = input.name.replace(/variants\[\d+\]/, `variants[${i}]`);
        });
    });
}

// =================== Review Update ===================
function updateReview() {
    const name = document.getElementById('name').value || '-';
    const category = document.getElementById('category_id');
    const categoryText = category.options[category.selectedIndex]?.text || '-';
    const price = document.getElementById('base_price').value;
    const description = document.getElementById('description').value || '-';
    const weight = document.getElementById('weight_grams').value;
    const status = document.getElementById('status').value;

    document.getElementById('reviewName').textContent = name;
    document.getElementById('reviewCategory').textContent = categoryText;
    document.getElementById('reviewPrice').textContent = price ? `Rp ${parseInt(price).toLocaleString('id-ID')}` : '-';
    document.getElementById('reviewDescription').textContent = description.substring(0, 200) + (description.length > 200 ? '...' : '');
    document.getElementById('reviewWeight').textContent = weight ? `${weight} gram` : '-';
    document.getElementById('reviewStatus').textContent = status === 'active' ? '🟢 Aktif (Publik)' : '📝 Draft (Privat)';

    const variants = document.querySelectorAll('.variant-card');
    document.getElementById('reviewVariants').textContent = variants.length === 1 ? '1 varian' : `${variants.length} varian`;

    // First image preview (file or URL)
    const firstRow = document.querySelector('.image-row');
    const reviewImg = document.getElementById('reviewImage');
    if (firstRow) {
        const fileInput = firstRow.querySelector('.file-input');
        const urlInput = firstRow.querySelector('.image-input');
        const src = fileInput && fileInput.files && fileInput.files[0]
            ? URL.createObjectURL(fileInput.files[0])
            : (urlInput ? urlInput.value.trim() : '');
        if (src) {
            reviewImg.innerHTML = `<img src="${src}" alt="" class="w-full h-full object-cover">`;
        }
    }
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

document.getElementById('productForm').addEventListener('submit', function (e) {
    const status = document.getElementById('status').value;
    const missing = [];

    const name = document.getElementById('name').value.trim();
    const category = document.getElementById('category_id').value;
    const price = document.getElementById('base_price').value;
    const weight = document.getElementById('weight_grams').value;

    if (!name) missing.push('Nama produk wajib diisi.');
    if (!category) missing.push('Pilih kategori produk.');

    if (status === 'active') {
        if (!price || parseFloat(price) <= 0) missing.push('Harga produk wajib diisi dan harus lebih dari 0.');
        if (!weight || parseInt(weight) <= 0) missing.push('Berat produk wajib diisi dan harus lebih dari 0.');

        const filledImageRows = document.querySelectorAll('.image-row');
        let hasImage = false;
        filledImageRows.forEach(row => {
            const file = row.querySelector('.file-input');
            const url = row.querySelector('.image-input');
            if ((file && file.files && file.files[0]) || (url && url.value.trim())) hasImage = true;
        });
        if (!hasImage) missing.push('Minimal satu gambar produk wajib diisi (upload file atau tempel URL).');

        const variantCards = document.querySelectorAll('.variant-card');
        variantCards.forEach(card => {
            const sku = card.querySelector('input[name$="[sku]"]');
            const stock = card.querySelector('input[name$="[stock]"]');
            if (!sku || !sku.value.trim()) missing.push('SKU pada setiap varian wajib diisi.');
            if (!stock || stock.value === '') missing.push('Stok pada setiap varian wajib diisi.');
        });
    }

    if (missing.length) {
        e.preventDefault();
        showBanner(missing);
    }
});
</script>
@endpush
@endsection
