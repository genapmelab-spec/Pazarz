<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $store = Store::withCount(['products', 'followers'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $store,
        ]);
    }

    public function products(Request $request, string $slug): JsonResponse
    {
        $store = Store::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $products = $store->products()
            ->with(['primaryImage', 'category'])
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->paginate(24);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }
}
