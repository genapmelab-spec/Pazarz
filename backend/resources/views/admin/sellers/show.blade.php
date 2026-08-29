@extends('layouts.app')
@section('title', 'Seller Details')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Seller: ' . ($seller->store->name ?? $seller->user->name))
@section('header-actions')
    <a href="{{ route('admin.sellers.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Sellers</a>
@endsection
@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Seller Information</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Name</p>
                <p class="font-medium">{{ $seller->user->name }}</p>
            </div>
            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium">{{ $seller->user->email }}</p>
            </div>
            <div>
                <p class="text-gray-500">Store Name</p>
                <p class="font-medium">{{ $seller->store->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                @php
                    $statusColors = ['verified' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'rejected' => 'bg-red-100 text-red-800'];
                @endphp
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$seller->verification_status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($seller->verification_status) }}</span>
            </div>
            <div>
                <p class="text-gray-500">Joined</p>
                <p class="font-medium">{{ $seller->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Actions</h3>
        <div class="flex gap-3">
            @if($seller->verification_status === 'pending')
                <form method="POST" action="{{ route('admin.sellers.approve', $seller) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Approve</button>
                </form>
                <form method="POST" action="{{ route('admin.sellers.reject', $seller) }}" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="reason" placeholder="Rejection reason..." required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700">Reject</button>
                </form>
            @elseif($seller->verification_status === 'verified')
                <form method="POST" action="{{ route('admin.sellers.reject', $seller) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700"
                        onclick="return confirm('Revoke verification?')">Revoke Verification</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
