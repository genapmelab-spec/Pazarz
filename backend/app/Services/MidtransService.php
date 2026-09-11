<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Midtrans Sandbox integration.
 *
 * The Server Key is used exclusively here (server-to-server). It is never
 * returned to the frontend; the checkout endpoint only ever exposes the
 * public Client Key, which Snap requires.
 */
class MidtransService
{
    /** Payments column is a plain string, so these are safe values. */
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    protected ?string $serverKey;

    protected string $apiBaseUrl;

    protected string $snapAppBaseUrl;

    public function __construct()
    {
        $this->serverKey = config('midtrans.server_key');
        $this->apiBaseUrl = rtrim((string) config('midtrans.api_base_url'), '/');
        $this->snapAppBaseUrl = rtrim((string) config('midtrans.snap_app_base_url'), '/');
    }

    /**
     * Create (or reuse) the Payment for the order and request a Snap token.
     *
     * Midtrans order_id is the Pazarz order number and stays constant across
     * retries, so a webhook always resolves the same Payment row. Retrying an
     * expired/failed payment re-requests a token for the same order_id.
     */
    public function createSnapTransaction(Order $order): array
    {
        $this->ensureConfigured();

        if ($order->status !== 'pending_payment') {
            throw new \Exception('Order #' . $order->order_number . ' is not awaiting payment.');
        }

        $payment = DB::transaction(function () use ($order) {
            $payment = Payment::where('order_id', $order->id)->lockForUpdate()->first();

            if (! $payment) {
                $payment = Payment::create([
                    'order_id' => $order->id,
                    'method' => 'midtrans',
                    'amount' => $order->grand_total,
                    'status' => self::STATUS_PENDING,
                ]);
            }

            if (in_array($payment->status, [self::STATUS_SUCCESS, self::STATUS_REFUNDED], true)) {
                throw new \Exception('This order has already been paid.');
            }

            // Resetting a terminal-negative payment makes it retryable while
            // keeping the same Midtrans order_id (no duplicate Payment rows).
            if ($payment->status !== self::STATUS_PENDING) {
                $payment->update(['status' => self::STATUS_PENDING, 'paid_at' => null]);
            }

            $payment->update([
                'provider' => 'midtrans',
                'provider_reference' => $order->order_number,
                'amount' => $order->grand_total,
            ]);

            return $payment;
        });

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round((float) $order->grand_total),
            ],
            'item_details' => $this->buildItemDetails($order),
            'customer_details' => $this->buildCustomerDetails($order),
            'expiry' => [
                'unit' => 'hours',
                'duration' => (int) config('midtrans.expiry_hours', 24),
            ],
        ];

        // Snap API: POST {app}/snap/v1/transactions with the Server Key as
        // HTTP Basic auth username (empty password).
        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->post("{$this->snapAppBaseUrl}/snap/v1/transactions", $payload);

        if (! $response->successful() || empty($response->json('token'))) {
            Log::error('Midtrans Snap token request failed', [
                'order_id' => $order->id,
                'http_status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);

            throw new \Exception('Failed to create Midtrans payment. Please try again.');
        }

        return [
            'token' => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
            'client_key' => config('midtrans.client_key'),
            'snap_js_url' => config('midtrans.snap_js_url'),
            'is_production' => (bool) config('midtrans.is_production'),
            'order_number' => $order->order_number,
            'amount' => $payment->amount,
        ];
    }

    /**
     * Ask Midtrans for the authoritative status of an order (server-to-server)
     * and apply it. Used by the frontend poll so a payment still settles
     * correctly on localhost, where Midtrans cannot reach our webhook.
     */
    public function syncOrderStatus(Order $order): ?Payment
    {
        $this->ensureConfigured();

        $payment = Payment::where('order_id', $order->id)->first();

        if (! $payment) {
            return null;
        }

        $response = Http::withBasicAuth($this->serverKey, '')
            ->acceptJson()
            ->get("{$this->apiBaseUrl}/v2/{$order->order_number}/status");

        if (! $response->successful()) {
            // 404 = Midtrans has no transaction for this order yet.
            return $payment;
        }

        $body = $response->json();

        $newStatus = $this->mapTransactionStatus(
            $body['transaction_status'] ?? null,
            $body['fraud_status'] ?? null
        );

        if ($newStatus === null) {
            return $payment;
        }

        $this->applyStatus(
            $payment,
            $newStatus,
            $body['payment_type'] ?? null,
            $body['transaction_id'] ?? null
        );

        return $payment->refresh();
    }

    /**
     * Verify and process a Midtrans HTTP notification. Idempotent: replayed and
     * out-of-order notifications are safe.
     */
    public function handleNotification(array $payload): array
    {
        $this->ensureConfigured();

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;

        if (! $orderId || ! $statusCode || $grossAmount === null) {
            throw new \Exception('Invalid notification payload.');
        }

        // Signature: sha512(order_id + status_code + gross_amount + server_key)
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);

        if (! is_string($signatureKey) || ! hash_equals($expected, $signatureKey)) {
            Log::warning('Midtrans notification signature mismatch', ['order_id' => $orderId]);
            throw new \Exception('Invalid signature.');
        }

        $payment = Payment::where('provider_reference', $orderId)->first();

        if (! $payment) {
            Log::warning('Midtrans notification for unknown payment', ['order_id' => $orderId]);
            throw new \Exception('Payment not found.');
        }

        $newStatus = $this->mapTransactionStatus(
            $payload['transaction_status'] ?? null,
            $payload['fraud_status'] ?? null
        );

        if ($newStatus === null) {
            Log::info('Midtrans notification ignored (unmapped status)', [
                'order_id' => $orderId,
                'transaction_status' => $payload['transaction_status'] ?? null,
            ]);

            return ['status' => 'ignored'];
        }

        $result = $this->applyStatus(
            $payment,
            $newStatus,
            $payload['payment_type'] ?? null,
            $payload['transaction_id'] ?? null
        );

        Log::info('Midtrans notification processed', [
            'order_id' => $orderId,
            'payment_status' => $newStatus,
            'midtrans_transaction_id' => $payload['transaction_id'] ?? null,
            'applied' => $result,
        ]);

        return ['status' => 'processed', 'payment_status' => $newStatus, 'applied' => $result];
    }

    /**
     * Apply a payment status transition and cascade to the Order. Runs inside a
     * row lock and only acts when the status actually changes, so duplicate
     * notifications can never double-deduct stock or double-advance the order.
     */
    protected function applyStatus(Payment $payment, string $newStatus, ?string $paymentType, ?string $transactionId): array
    {
        return DB::transaction(function () use ($payment, $newStatus, $paymentType, $transactionId) {
            $payment = Payment::whereKey($payment->getKey())->lockForUpdate()->first();

            if (! $this->shouldTransition($payment->status, $newStatus)) {
                return ['order_id' => $payment->getKey(), 'payment_status' => $payment->status, 'changed' => false];
            }

            $payment->update([
                'status' => $newStatus,
                'method' => $paymentType ? 'midtrans:' . $paymentType : $payment->method,
                'paid_at' => $newStatus === self::STATUS_SUCCESS ? now() : $payment->paid_at,
            ]);

            $order = $payment->order;

            if ($newStatus === self::STATUS_SUCCESS) {
                $this->markOrderPaid($order);
            } elseif ($newStatus === self::STATUS_EXPIRED) {
                // An expired transaction stays retryable: the order keeps
                // awaiting payment (and its reserved stock) so the customer can
                // generate a fresh Snap token for the same Midtrans order_id.
                \App\Models\Notification::createForUser(
                    $order->user,
                    'payment_expired',
                    'Payment Expired',
                    "Payment for order #{$order->order_number} expired. You can try again.",
                    ['order_id' => $order->id, 'order_number' => $order->order_number]
                );
            } elseif (in_array($newStatus, [self::STATUS_FAILED, self::STATUS_CANCELLED], true)) {
                $this->markOrderUnpaid($order);
            }

            return ['order_id' => $payment->id, 'payment_status' => $newStatus, 'changed' => true];
        });
    }

    protected function markOrderPaid(Order $order): void
    {
        // Accept both the normal path and a late settlement after we had
        // cancelled an unpaid order (the reserved stock was released then, so
        // deducting now restores consistency).
        if (! in_array($order->status, ['pending_payment', 'cancelled'], true)) {
            return;
        }

        $order->update(['status' => 'paid']);

        foreach ($order->subOrders as $subOrder) {
            if ($subOrder->status === 'cancelled') {
                continue;
            }

            foreach ($subOrder->items as $item) {
                $inventory = $item->variant?->inventory;
                $inventory?->deduct($item->quantity);
            }
        }

        \App\Models\Notification::createForUser(
            $order->user,
            'payment_success',
            'Payment Successful',
            "Your order #{$order->order_number} has been paid.",
            ['order_id' => $order->id, 'order_number' => $order->order_number]
        );
    }

    protected function markOrderUnpaid(Order $order): void
    {
        if ($order->status !== 'pending_payment') {
            return;
        }

        $order->update(['status' => 'cancelled']);

        foreach ($order->subOrders as $subOrder) {
            if ($subOrder->status === 'cancelled') {
                continue;
            }

            foreach ($subOrder->items as $item) {
                $item->variant?->inventory?->release($item->quantity);
            }
        }

        \App\Models\Notification::createForUser(
            $order->user,
            'payment_failed',
            'Payment Failed',
            "Payment for order #{$order->order_number} was not completed.",
            ['order_id' => $order->id, 'order_number' => $order->order_number]
        );
    }

    /**
     * Map a Midtrans transaction_status (+ fraud_status) to a payment status.
     */
    protected function mapTransactionStatus(?string $transactionStatus, ?string $fraudStatus): ?string
    {
        return match ($transactionStatus) {
            'capture' => ($fraudStatus ?? 'accept') === 'challenge'
                ? self::STATUS_PENDING
                : self::STATUS_SUCCESS,
            'settlement' => self::STATUS_SUCCESS,
            'pending' => self::STATUS_PENDING,
            'deny' => self::STATUS_FAILED,
            'cancel' => self::STATUS_CANCELLED,
            'expire' => self::STATUS_EXPIRED,
            'refund', 'partial_refund' => self::STATUS_REFUNDED,
            default => null,
        };
    }

    /**
     * Allowed payment-status transitions. A repeated status is a no-op, and a
     * retried payment can still reach success after failed/expired/cancelled.
     */
    protected function shouldTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return false;
        }

        return match ($from) {
            self::STATUS_PENDING => in_array($to, [
                self::STATUS_SUCCESS,
                self::STATUS_FAILED,
                self::STATUS_EXPIRED,
                self::STATUS_CANCELLED,
                self::STATUS_REFUNDED,
            ], true),
            self::STATUS_FAILED, self::STATUS_EXPIRED, self::STATUS_CANCELLED => in_array($to, [
                self::STATUS_PENDING,
                self::STATUS_SUCCESS,
                self::STATUS_REFUNDED,
            ], true),
            self::STATUS_SUCCESS => $to === self::STATUS_REFUNDED,
            default => false,
        };
    }

    protected function buildItemDetails(Order $order): array
    {
        $items = [];

        foreach ($order->subOrders as $subOrder) {
            foreach ($subOrder->items as $item) {
                $items[] = [
                    'id' => (string) $item->product_variant_id,
                    'price' => (int) round((float) $item->price_snapshot),
                    'quantity' => $item->quantity,
                    'name' => mb_substr(
                        $item->product_name_snapshot . ($item->variant_label_snapshot ? ' - ' . $item->variant_label_snapshot : ''),
                        0,
                        50
                    ),
                ];
            }

            if ((float) $subOrder->shipping_cost > 0) {
                $items[] = [
                    'id' => 'SHIP-' . $subOrder->id,
                    'price' => (int) round((float) $subOrder->shipping_cost),
                    'quantity' => 1,
                    'name' => 'Shipping',
                ];
            }
        }

        if ((float) $order->discount_total > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) round((float) $order->discount_total),
                'quantity' => 1,
                'name' => 'Discount',
            ];
        }

        // Midtrans requires the item sum to equal gross_amount when supplied.
        $sum = array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $items));
        $target = (int) round((float) $order->grand_total);

        if ($sum !== $target) {
            return [[
                'id' => 'ORDER-' . $order->order_number,
                'price' => $target,
                'quantity' => 1,
                'name' => 'Order ' . $order->order_number,
            ]];
        }

        return $items;
    }

    protected function buildCustomerDetails(Order $order): array
    {
        $address = $order->shippingAddress;

        $details = [
            'first_name' => $order->user->name,
            'email' => $order->user->email,
        ];

        if ($address) {
            $details['shipping_address'] = [
                'first_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address' => $address->full_address ?? ($address->address_line ?? ''),
                'city' => $address->city,
                'postal_code' => $address->postal_code,
                'country_code' => 'IDN',
            ];
        }

        return $details;
    }

    protected function ensureConfigured(): void
    {
        if (empty($this->serverKey)) {
            throw new \Exception('Midtrans is not configured (MIDTRANS_SERVER_KEY is empty).');
        }
    }
}
