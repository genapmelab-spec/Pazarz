<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\SubOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sub_order_id' => 'required|exists:sub_orders,id',
            'reason' => 'required|string|max:500',
            'description' => 'required|string|max:2000',
        ]);

        $subOrder = SubOrder::with('order')->findOrFail($validated['sub_order_id']);

        // Verify ownership
        if ($subOrder->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Not your order.'],
            ], 403);
        }

        // Check if already has open dispute
        $existingDispute = Dispute::where('sub_order_id', $subOrder->id)
            ->whereIn('status', ['open', 'in_review'])
            ->exists();

        if ($existingDispute) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'DISPUTE_EXISTS', 'message' => 'A dispute already exists for this order.'],
            ], 422);
        }

        $dispute = Dispute::create([
            'sub_order_id' => $subOrder->id,
            'raised_by' => $request->user()->id,
            'reason' => $validated['reason'],
            'status' => 'open',
        ]);

        // Notify admin and seller
        // In production, this would be done via events/notifications

        return response()->json([
            'success' => true,
            'data' => $dispute,
        ], 201);
    }
}
