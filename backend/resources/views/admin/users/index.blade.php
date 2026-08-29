@extends('layouts.app')
@section('title', 'Users')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'Users')
@section('content')
<div class="bg-white rounded-2xl border border-gray-100">
    <div class="p-6 border-b border-gray-100">
        <h2 class="text-lg font-semibold">All Users</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">User</th>
                    <th class="px-6 py-3 font-medium">Email</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Joined</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center text-sm font-medium">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="text-sm font-medium">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            @php
                                $roleColors = ['admin' => 'bg-purple-100 text-purple-800', 'seller' => 'bg-blue-100 text-blue-800', 'customer' => 'bg-gray-100 text-gray-800'];
                            @endphp
                            @foreach($user->roles as $role)
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-800' }} mr-1">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </td>
                        <td class="px-6 py-4">
                            @if($user->isActive())
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                            @else
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Suspended</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500 text-sm">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">{{ $users->links() }}</div>
</div>
@endsection
