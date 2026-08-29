@extends('layouts.app')
@section('title', 'Platform Settings')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Platform Settings')
@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl">@csrf @method('PUT')
    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Commission Rate (%)</label>
            <input type="number" name="commission_rate" value="{{ $settings['commission_rate'] }}" min="0" max="100" step="0.1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            <p class="text-xs text-gray-500 mt-1">Percentage taken from each seller's sale when sub-order is completed.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dispute Window (days)</label>
            <input type="number" name="dispute_window_days" value="{{ $settings['dispute_window_days'] }}" min="1" max="90" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            <p class="text-xs text-gray-500 mt-1">Number of days after delivery during which a customer can raise a dispute.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Auto-Complete Order (days)</label>
            <input type="number" name="auto_complete_days" value="{{ $settings['auto_complete_days'] }}" min="1" max="90" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            <p class="text-xs text-gray-500 mt-1">Number of days after delivery before order is automatically marked as completed.</p>
        </div>
        <div class="pt-2">
            <button type="submit" class="bg-black text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-gray-800">Save Settings</button>
        </div>
    </div>
</form>
@endsection
