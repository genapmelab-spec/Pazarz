<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->user()->seller->store;

        $promotions = Promotion::with('products')
            ->where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('seller.promotions.index', compact('promotions', 'store'));
    }

    public function create()
    {
        $products = Product::where('store_id', auth()->user()->seller->store->id)
            ->where('status', 'active')
            ->get();

        return view('seller.promotions.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        $store = auth()->user()->seller->store;

        $promotion = Promotion::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'value' => $validated['value'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'status' => 'active',
        ]);

        $promotion->products()->sync($validated['product_ids']);

        return redirect()->route('seller.promotions.index')
            ->with('success', 'Promotion created.');
    }
}
