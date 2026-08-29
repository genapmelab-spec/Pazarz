<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['store', 'category', 'primaryImage'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('name', 'LIKE', "%{$s}%"))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.products.index', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load(['store.seller', 'category', 'images', 'variants.inventory']);

        return view('admin.products.show', compact('product'));
    }

    public function deactivate(Request $request, Product $product)
    {
        $oldStatus = $product->status;
        $product->update(['status' => 'inactive']);

        AuditLog::log($request->user(), 'deactivate_product', $product, [
            'status' => ['old' => $oldStatus, 'new' => 'inactive'],
        ], $request->ip());

        return redirect()->back()->with('success', 'Product deactivated.');
    }

    public function activate(Request $request, Product $product)
    {
        $oldStatus = $product->status;
        $product->update(['status' => 'active']);

        AuditLog::log($request->user(), 'activate_product', $product, [
            'status' => ['old' => $oldStatus, 'new' => 'active'],
        ], $request->ip());

        return redirect()->back()->with('success', 'Product activated.');
    }
}
