<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(protected MidtransService $midtrans) {}

    /**
     * Start (or retry) payment for an order. Returns a Snap token for the
     * frontend to open Midtrans Snap. Only the public Client Key is exposed —
     * the Server Key never leaves the backend.
     */
    public function pay(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->resolveOrder($request, $orderNumber);

        if (! $order) {
            return $this->notFound();
        }

        try {
            $payload = $this->midtrans->createSnapTransaction($order);

            return response()->json([
                'success' => true,
                'data' => $payload,
            ]);
        } catch (\Exception $e) {
            Log::warning('Midtrans payment init failed', [
                'order_number' => $orderNumber,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => ['code' => 'PAYMENT_INIT_FAILED', 'message' => $e->getMessage()],
            ], 422);
        }
    }

    /**
     * Authoritative payment/order status for the customer. Falls back to a
     * server-to-server Midtrans status check, so a payment still settles on
     * localhost where Midtrans cannot reach the webhook. Never trusts any
     * frontend-supplied "paid" claim.
     */
    public function status(Request $request, string $orderNumber): JsonResponse
    {
        $order = $this->resolveOrder($request, $orderNumber);

        if (! $order) {
            return $this->notFound();
        }

        if ($order->status === 'pending_payment') {
            try {
                $this->midtrans->syncOrderStatus($order);
                $order->refresh();
            } catch (\Exception $e) {
                Log::info('Midtrans status sync skipped', [
                    'order_number' => $orderNumber,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $payment = Payment::where('order_id', $order->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'order_status' => $order->status,
                'payment_status' => $payment?->status ?? 'pending',
                'payment_method' => $payment?->method,
                'amount' => $payment?->amount,
                'paid_at' => $payment?->paid_at?->toIso8601String(),
                'retryable' => (bool) ($payment?->isRetryable() ?? true) && $order->status === 'pending_payment',
            ],
        ]);
    }

    /**
     * Midtrans HTTP notification webhook. Public (no user auth) but protected
     * by Midtrans signature verification; idempotent by design.
     */
    public function notification(Request $request): JsonResponse
    {
        try {
            $result = $this->midtrans->handleNotification($request->all());

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            // 403 for signature/payload problems so Midtrans does not retry a
            // request we can never accept; Midtrans expects a 2xx on success.
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    protected function resolveOrder(Request $request, string $orderNumber): ?Order
    {
        return Order::with(['subOrders.items.variant.inventory', 'shippingAddress', 'user'])
            ->where('order_number', $orderNumber)
            ->where('user_id', $request->user()->id)
            ->first();
    }

    protected function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => ['code' => 'ORDER_NOT_FOUND', 'message' => 'Order not found.'],
        ], 404);
    }
}
