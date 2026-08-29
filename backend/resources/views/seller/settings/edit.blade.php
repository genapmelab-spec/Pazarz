@extends('layouts.app')
@section('title', 'Store Settings')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'settings'])
@endsection
@section('header', 'Store Settings')
@section('content')
<form method="POST" action="{{ route('seller.settings.update') }}" class="max-w-2xl">@csrf @method('PUT')
    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-5">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">{{ old('description', $store->description) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo URL</label>
            <input type="text" name="logo_url" value="{{ old('logo_url', $store->logo_url) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Banner URL</label>
            <input type="text" name="banner_url" value="{{ old('banner_url', $store->banner_url) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
        </div>
        <div class="pt-2">
            <button type="submit" class="bg-black text-white px-6 py-3 rounded-full text-sm font-medium hover:bg-gray-800">Save Changes</button>
        </div>
    </div>
</form>
@endsection
