<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_address_id' => 'required|exists:addresses,id',
            'shipping_methods' => 'required|array',
            'shipping_methods.*.store_id' => 'required|exists:stores,id',
            'shipping_methods.*.courier' => 'required|string',
            'shipping_methods.*.cost' => 'required|numeric|min:0',
            'coupon_code' => 'nullable|string',
        ]);

        try {
            $result = $this->checkoutService->checkout(
                $request->user(),
                $validated['shipping_address_id'],
                collect($validated['shipping_methods'])->pluck(null, 'store_id')->toArray(),
                $validated['coupon_code'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ], 201);
        } catch (\App\Exceptions\InsufficientStockException $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INSUFFICIENT_STOCK', 'message' => $e->getMessage()],
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'CHECKOUT_FAILED', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    public function paymentCallback(Request $request): JsonResponse
    {
        // In production, verify webhook signature from payment gateway
        $this->checkoutService->handlePaymentCallback(
            $request->input('provider_reference'),
            $request->input('status'),
            $request->all()
        );

        return response()->json(['success' => true]);
    }

    public function webhook(Request $request): JsonResponse
    {
        // Idempotent webhook handler
        $this->checkoutService->handlePaymentCallback(
            $request->input('provider_reference'),
            $request->input('status'),
            $request->all()
        );

        return response()->json(['success' => true]);
    }
}
