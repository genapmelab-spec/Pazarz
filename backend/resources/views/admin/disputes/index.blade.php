@extends('layouts.app')
@section('title', 'Disputes')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Disputes')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Order #</th>
                    <th class="px-6 py-3 font-medium">Customer</th>
                    <th class="px-6 py-3 font-medium">Reason</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disputes as $dispute)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $dispute->order->order_number ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm">{{ $dispute->raiser->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($dispute->reason, 50) }}</td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = ['open' => 'bg-yellow-100 text-yellow-800', 'resolved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                            @endphp
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$dispute->status] ?? 'bg-gray-100 text-gray-800' }}">{{ ucfirst($dispute->status) }}</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $dispute->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.disputes.show', $dispute) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No disputes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $disputes->links() }}</div>
</div>
@endsection
