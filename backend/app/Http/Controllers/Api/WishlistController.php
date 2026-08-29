<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlist = $request->user()->wishlist;
        if (!$wishlist) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $items = $wishlist->items()->with('product.store', 'product.primaryImage', 'product.images')->get();

        // Return product objects for frontend compatibility
        $products = $items->map(fn($item) => $item->product)->filter()->values();

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = $request->user()->wishlist;
        if (!$wishlist) {
            $wishlist = $request->user()->wishlist()->create();
        }

        $existing = $wishlist->items()->where('product_id', $request->product_id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['success' => true, 'data' => ['action' => 'removed']]);
        }

        $wishlist->items()->create(['product_id' => $request->product_id]);
        return response()->json(['success' => true, 'data' => ['action' => 'added']], 201);
    }

    public function addToCart(Request $request, $product): JsonResponse
    {
        $wishlist = $request->user()->wishlist;
        $item = $wishlist?->items()->where('product_id', $product)->first();

        if ($item) {
            $item->delete();
        }

        // Add first variant to cart
        $productModel = \App\Models\Product::with('variants')->findOrFail($product);
        $variant = $productModel->variants->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NO_VARIANTS', 'message' => 'Product has no variants.'],
            ], 422);
        }

        $cartService = app(\App\Services\CartService::class);
        $cart = $cartService->addItem($request->user(), $variant->id, 1);

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }
}
