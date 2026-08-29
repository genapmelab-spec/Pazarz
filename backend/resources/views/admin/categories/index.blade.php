@extends('layouts.app')
@section('title', 'Categories')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Categories')
@section('header-actions')
    <a href="{{ route('admin.categories.create') }}" class="bg-black text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800">+ Add Category</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Parent</th>
                    <th class="px-6 py-3 font-medium">Sort</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Products</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $category->parent->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $category->sort_order }}</td>
                        <td class="px-6 py-4">
                            @if($category->is_active)
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $category->products_count ?? $category->products->count() }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600 hover:underline">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No categories found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
