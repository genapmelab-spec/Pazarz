@extends('layouts.app')

@section('title', 'Pending Verification')

@section('content')
<div class="flex items-center justify-center min-h-[60vh]">
    <div class="text-center max-w-md">
        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h2 class="text-xl font-semibold mb-2">Verification Pending</h2>
        <p class="text-gray-500 mb-6">
            Your seller account is being reviewed by our admin team. You will be notified once your account is verified.
        </p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-600 hover:text-red-600">Logout</button>
        </form>
    </div>
</div>
@endsection
