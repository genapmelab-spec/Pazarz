<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Services\ProductService;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'status' => 'in:draft,active',
            'images' => 'nullable|array',
            'images.*' => 'string',
            'variants' => 'nullable|array',
            'variants.*.sku' => 'required_with:variants|string|max:50',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $store = $request->user()->seller->store;
        $product = $this->productService->createProduct($store, $validated);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product created successfully.');
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

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'base_price' => 'required|numeric|min:0',
            'weight_grams' => 'required|integer|min:0',
            'status' => 'in:draft,active,inactive',
        ]);

        $this->productService->updateProduct($product, $validated);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->update(['status' => 'archived']);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product archived.');
    }
}
