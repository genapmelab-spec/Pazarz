<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->getCustomerOrders($request->user(), $request->status);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->orderService->getOrderByNumber($orderNumber, $request->user());

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Order not found.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    public function complete(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->orderService->getOrderByNumber($orderNumber, $request->user());

        if (!$order) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Order not found.'],
            ], 404);
        }

        try {
            $order = $this->orderService->completeOrder($order);

            return response()->json([
                'success' => true,
                'data' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'CANNOT_COMPLETE', 'message' => $e->getMessage()],
            ], 422);
        }
    }
}
