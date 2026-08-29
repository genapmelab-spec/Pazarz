@extends('layouts.app')
@section('title', 'Audit Logs')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Audit Logs')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Time</th>
                    <th class="px-6 py-3 font-medium">User</th>
                    <th class="px-6 py-3 font-medium">Action</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-sm">{{ $log->actor->name ?? 'System' }}</td>
                        <td class="px-6 py-4 text-sm font-medium">{{ $log->action }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ class_basename($log->subject_type) ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit(json_encode($log->changes), 60) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 text-sm">No audit logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $logs->links() }}</div>
</div>
@endsection
