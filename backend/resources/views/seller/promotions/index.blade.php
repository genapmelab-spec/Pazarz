@extends('layouts.app')
@section('title', 'Promotions')
@section('sidebar')
    @include('seller._sidebar', ['active' => 'promotions'])
@endsection
@section('header', 'Promotions')
@section('header-actions')
    <a href="{{ route('seller.promotions.create') }}" class="bg-black text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-800">+ Create Promotion</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Name</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Value</th>
                    <th class="px-6 py-3 font-medium">Period</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($promotions as $promo)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium">{{ $promo->name }}</td>
                        <td class="px-6 py-4 text-sm">{{ ucfirst($promo->type) }}</td>
                        <td class="px-6 py-4 text-sm">{{ $promo->type === 'percentage' ? $promo->value . '%' : 'Rp ' . number_format($promo->value, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $promo->starts_at?->format('d M') ?? '—' }} — {{ $promo->ends_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $promo->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">{{ ucfirst($promo->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">No promotions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
