@extends('layouts.app')
@section('title', 'Reviews')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'reviews'])
@endsection
@section('header', 'Reviews')
@section('content')
<div class="space-y-4">
    @forelse($reviews as $review)
        <div class="bg-white rounded-2xl border border-gray-100 p-6">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="font-medium text-sm">{{ $review->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $review->product->name }} · {{ $review->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                        <span class="text-{{ $i <= $review->rating ? 'yellow-400' : 'gray-300' }}">★</span>
                    @endfor
                </div>
            </div>
            <p class="text-sm text-gray-700 mb-4">{{ $review->comment }}</p>

            @if($review->seller_reply)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 mb-1">Your Reply</p>
                    <p class="text-sm">{{ $review->seller_reply }}</p>
                </div>
            @else
                <form method="POST" action="{{ route('seller.reviews.reply', $review) }}" class="mt-4">@csrf
                    <textarea name="seller_reply" rows="2" placeholder="Write a reply..." class="w-full px-3 py-2 border rounded-lg text-sm mb-2" required></textarea>
                    <button class="bg-black text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800">Reply</button>
                </form>
            @endif
        </div>
    @empty
        <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center text-gray-500 text-sm">No reviews yet.</div>
    @endforelse
</div>
<div class="mt-4">{{ $reviews->links() }}</div>
@endsection
