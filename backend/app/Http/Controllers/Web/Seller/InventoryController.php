<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->user()->seller->store;

        $variants = Inventory::with(['variant.product', 'variant.attributeValues.attribute'])
            ->whereHas('variant.product', fn($q) => $q->where('store_id', $store->id))
            ->when($request->low_stock, fn($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold'))
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('seller.inventory.index', compact('variants', 'store'));
    }

    public function update(Request $request, Inventory $inventory)
    {
        $this->authorize('manageStock', $inventory->variant->product);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
        ]);

        $inventory->update([
            'quantity' => $validated['quantity'],
            'low_stock_threshold' => $validated['low_stock_threshold'] ?? $inventory->low_stock_threshold,
        ]);

        return redirect()->back()->with('success', 'Inventory updated.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.inventory_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $inventory = Inventory::findOrFail($item['inventory_id']);
            $this->authorize('manageStock', $inventory->variant->product);
            $inventory->update(['quantity' => $item['quantity']]);
        }

        return redirect()->back()->with('success', 'Inventory bulk updated.');
    }
}
