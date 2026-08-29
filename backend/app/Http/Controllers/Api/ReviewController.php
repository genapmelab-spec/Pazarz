<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::with(['user', 'product', 'images'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews->items(),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
                'last_page' => $reviews->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_item_id' => 'required|exists:order_items,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string',
        ]);

        $orderItem = OrderItem::with('subOrder.order')->findOrFail($validated['order_item_id']);

        // Verify ownership
        if ($orderItem->subOrder->order->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => 'Not your order item.'],
            ], 403);
        }

        // Verify order is completed
        if ($orderItem->subOrder->status !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_ELIGIBLE', 'message' => 'Order must be completed before reviewing.'],
            ], 422);
        }

        // Check if already reviewed
        if ($orderItem->hasReview()) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'ALREADY_REVIEWED', 'message' => 'You have already reviewed this item.'],
            ], 422);
        }

        $variant = $orderItem->variant;
        $product = $variant->product;

        $review = Review::create([
            'order_item_id' => $orderItem->id,
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'status' => 'visible',
        ]);

        // Create review images
        if (!empty($validated['images'])) {
            foreach ($validated['images'] as $imageUrl) {
                $review->images()->create(['url' => $imageUrl]);
            }
        }

        // Update product rating
        $avgRating = Review::where('product_id', $product->id)->where('status', 'visible')->avg('rating');
        $countRating = Review::where('product_id', $product->id)->where('status', 'visible')->count();
        $product->update([
            'rating_avg' => round($avgRating, 2),
            'rating_count' => $countRating,
        ]);

        // Notify seller
        if ($product->store && $product->store->seller && $product->store->seller->user) {
            \App\Models\Notification::createForUser(
                $product->store->seller->user,
                'new_review',
                'New Review',
                "Your product {$product->name} received a {$validated['rating']}-star review.",
                ['product_id' => $product->id, 'review_id' => $review->id]
            );
        }

        return response()->json([
            'success' => true,
            'data' => $review->load('images'),
        ], 201);
    }
}
