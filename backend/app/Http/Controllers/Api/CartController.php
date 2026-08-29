<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCart($request->user());
        $cart->load('items.variant.product.store', 'items.variant.product.images', 'items.variant.inventory', 'items.variant.attributeValues.attribute');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'items' => $cart->items,
                'subtotal' => $cart->subtotal,
                'total_items' => $cart->total_items,
            ],
        ]);
    }

    public function addItem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            $cart = $this->cartService->addItem(
                $request->user(),
                $validated['product_variant_id'],
                $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'data' => $cart,
            ], 201);
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_STOCK', 'message' => $e->getMessage()],
            ], 409);
        }
    }

    public function updateItem(Request $request, $cartItem): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        try {
            $cart = $this->cartService->updateItem(
                $request->user(),
                $cartItem,
                $validated['quantity']
            );

            return response()->json([
                'success' => true,
                'data' => $cart,
            ]);
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_STOCK', 'message' => $e->getMessage()],
            ], 409);
        }
    }

    public function removeItem(Request $request, $cartItem): JsonResponse
    {
        $cart = $this->cartService->removeItem($request->user(), $cartItem);

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->clear($request->user());

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }
}
