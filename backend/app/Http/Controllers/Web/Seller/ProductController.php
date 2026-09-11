<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request)
    {
        $store = $request->user()->seller->store;

        $products = Product::with(['category', 'primaryImage', 'variants.inventory'])
            ->where('store_id', $store->id)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('seller.products.index', compact('products', 'store'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
        $attributes = ProductAttribute::with('category')->get();

        return view('seller.products.create', compact('categories', 'attributes'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateProductRequest($request, 'store');

        // Store uploaded images and collect image URLs (preserving row order)
        $validated['images'] = $this->processImages($request);

        // Only keep variant rows that actually have a SKU filled in
        $validated['variants'] = collect($validated['variants'] ?? [])
            ->filter(fn($variant) => !empty($variant['sku']))
            ->values()
            ->all();

        // product_variants.sku is globally unique — fail with a friendly message instead of a SQL error
        if (!empty($validated['variants'])) {
            $skus = array_column($validated['variants'], 'sku');
            if (count($skus) !== count(array_unique($skus))) {
                return back()->withErrors(['variants' => 'SKU varian tidak boleh sama dalam satu produk.'])->withInput();
            }
            if (ProductVariant::whereIn('sku', $skus)->exists()) {
                return back()->withErrors(['variants' => 'Salah satu SKU sudah dipakai produk lain. Gunakan SKU yang unik.'])->withInput();
            }
        }

        if ($validated['status'] === 'active' && empty($validated['images'])) {
            return back()->withErrors(['images' => 'Minimal satu gambar produk wajib diisi untuk mempublikasikan.'])->withInput();
        }

        $store = $request->user()->seller->store;
        $this->productService->createProduct($store, $validated);

        $message = $validated['status'] === 'active'
            ? 'Produk berhasil dipublikasikan.'
            : 'Produk berhasil disimpan sebagai draft.';

        return redirect()->route('seller.products.index')->with('success', $message);
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::where('is_active', true)->whereNull('parent_id')->with('children')->get();
        $attributes = ProductAttribute::with('category')->get();
        $product->load(['variants.inventory', 'variants.attributeValues.attribute', 'images']);

        return view('seller.products.edit', compact('product', 'categories', 'attributes'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $this->validateProductRequest($request, 'update');

        // Store uploaded images and collect image URLs (preserving row order)
        $validated['images'] = $this->processImages($request);

        if ($validated['status'] === 'active' && empty($validated['images'])) {
            return back()->withErrors(['images' => 'Minimal satu gambar produk wajib diisi untuk mempublikasikan.'])->withInput();
        }

        $this->productService->updateProduct($product, $validated);

        $message = $validated['status'] === 'active'
            ? 'Produk berhasil dipublikasikan.'
            : 'Produk berhasil disimpan sebagai draft.';

        return redirect()->route('seller.products.index')->with('success', $message);
    }

    /**
     * Shared validation: a draft only needs name + category, publishing needs everything.
     */
    protected function validateProductRequest(Request $request, string $mode): array
    {
        $isActive = $request->input('status') === 'active';

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'status' => 'in:draft,active' . ($mode === 'update' ? ',inactive' : ''),
            'images' => 'nullable|array',
            'images.*.url' => 'nullable|string|max:2000',
            'images.*.file' => 'nullable|image|max:5120',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'nullable|string|max:50',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.attributes' => 'nullable|array',
        ];

        if ($isActive) {
            $rules['base_price'] = 'required|numeric|min:1';
            $rules['weight_grams'] = 'required|integer|min:1';

            // Variants are only required when creating (they are managed via the Inventory page afterwards)
            if ($mode === 'store') {
                $rules['variants'] = 'required|array|min:1';
                $rules['variants.*.sku'] = 'required|string|max:50';
                $rules['variants.*.stock'] = 'required|integer|min:0';
            }
        } else {
            $rules['base_price'] = 'nullable|numeric|min:0';
            $rules['weight_grams'] = 'nullable|integer|min:0';
        }

        $validated = $request->validate($rules);

        // Defaults so partial drafts can always be persisted
        $validated['base_price'] = $validated['base_price'] ?? 0;
        $validated['weight_grams'] = $validated['weight_grams'] ?? 0;
        $validated['status'] = $validated['status'] ?? 'draft';

        return $validated;
    }

    /**
     * Merge file uploads and pasted URLs from each image row into a flat,
     * order-preserving list of image URLs (uploaded files are moved to /storage).
     */
    protected function processImages(Request $request): array
    {
        $urls = [];
        foreach ($request->input('images', []) as $index => $entry) {
            $file = $request->file("images.$index.file");
            $url = trim(is_array($entry) ? ($entry['url'] ?? '') : $entry);

            if ($file && $file->isValid()) {
                $path = $file->store('products', 'public');
                $urls[] = Storage::disk('public')->url($path);
            } elseif ($url !== '') {
                $urls[] = $url;
            }
        }

        return $urls;
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->update(['status' => 'archived']);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product archived.');
    }
}
