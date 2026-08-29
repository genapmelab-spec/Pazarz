@extends('layouts.app')
@section('title', 'Sellers')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Sellers')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Seller</th>
                    <th class="px-6 py-3 font-medium">Store</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Products</th>
                    <th class="px-6 py-3 font-medium">Joined</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sellers as $seller)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $seller->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $seller->store->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            @php$statusColors = ['verified' => 'bg-green-100 text-green-800', 'pending' => 'bg-yellow-100 text-yellow-800', 'rejected' => 'bg-red-100 text-red-800'];
            @endphp
            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$seller->verification_status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($seller->verification_status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm">{{ $seller->store?->products_count ?? $seller->store?->products?->count() ?? 0 }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $seller->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.sellers.show', $seller) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No sellers found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $sellers->links() }}</div>
</div>
@endsection
