<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $store = $request->user()->seller->store;

        $reviews = Review::with(['user', 'product', 'images'])
            ->whereHas('product', fn($q) => $q->where('store_id', $store->id))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('seller.reviews.index', compact('reviews', 'store'));
    }

    public function reply(Request $request, Review $review)
    {
        $this->authorize('reply', $review);

        $validated = $request->validate([
            'seller_reply' => 'required|string|max:1000',
        ]);

        $review->update(['seller_reply' => $validated['seller_reply']]);

        return redirect()->back()->with('success', 'Reply posted.');
    }
}
