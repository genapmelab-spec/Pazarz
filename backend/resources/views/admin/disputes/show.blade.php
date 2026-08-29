@extends('layouts.app')
@section('title', 'Dispute Details')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Dispute: #' . $dispute->id)
@section('header-actions')
    <a href="{{ route('admin.disputes.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Disputes</a>
@endsection
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Dispute Information</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Order</p>
                <p class="font-medium">{{ $dispute->order->order_number ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Customer</p>
                <p class="font-medium">{{ $dispute->raiser->name ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <p class="text-gray-500">Reason</p>
                <p class="font-medium">{{ $dispute->reason }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                @php
                    $statusColors = ['open' => 'bg-yellow-100 text-yellow-800', 'resolved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                @endphp
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$dispute->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($dispute->status) }}</span>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Messages</h3>
        <div class="space-y-3 mb-4">
            @forelse($dispute->messages ?? [] as $message)
                <div class="border border-gray-200 rounded-lg p-3 text-sm">
                    <p class="font-medium">{{ $message->sender->name ?? 'Admin' }}</p>
                    <p class="text-gray-600">{{ $message->message }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $message->created_at->format('M d, Y H:i') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">No messages yet.</p>
            @endforelse
        </div>
        @if($dispute->status === 'open')
            <form method="POST" action="{{ route('admin.disputes.message', $dispute) }}">
                @csrf
                <textarea name="message" rows="3" placeholder="Type your message..." required
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none mb-3"></textarea>
                <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">Send Message</button>
            </form>
        @endif
    </div>

    <!-- Actions -->
    @if($dispute->status === 'open')
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Actions</h3>
        <div class="flex gap-3">
            <form method="POST" action="{{ route('admin.disputes.resolve', $dispute) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Resolve</button>
            </form>
            <form method="POST" action="{{ route('admin.disputes.reject', $dispute) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700"
                    onclick="return confirm('Reject this dispute?')">Reject</button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
