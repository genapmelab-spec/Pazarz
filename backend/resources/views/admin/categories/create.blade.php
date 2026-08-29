@extends('layouts.app')
@section('title', 'Add Category')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Add Category')
@section('header-actions')
    <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Categories</a>
@endsection
@section('content')
<form method="POST" action="{{ route('admin.categories.store') }}" class="max-w-xl">
    @csrf
    <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none @error('name') border-red-500 @enderror">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Parent Category</label>
            <select name="parent_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                <option value="">None (Top Level)</option>
                @foreach($parentCategories as $cat)
                    <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="is_active" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-transparent outline-none">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="bg-black text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-gray-800 transition">Create Category</button>
            <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-600 hover:text-black">Cancel</a>
        </div>
    </div>
</form>
@endsection
