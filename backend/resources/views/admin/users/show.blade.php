@extends('layouts.app')
@section('title', 'User Details')
@section('sidebar')
    @include('admin._sidebar')
@endsection
@section('header', 'User: ' . $user->name)
@section('header-actions')
    <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 hover:text-black">← Back to Users</a>
@endsection
@section('content')
<div class="max-w-3xl space-y-6">
    <!-- User Info -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">User Information</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Name</p>
                <p class="font-medium">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-gray-500">Email</p>
                <p class="font-medium">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-gray-500">Phone</p>
                <p class="font-medium">{{ $user->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Joined</p>
                <p class="font-medium">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
            <div>
                <p class="text-gray-500">Status</p>
                @if($user->isActive())
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                @else
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Suspended</span>
                @endif
            </div>
            <div>
                <p class="text-gray-500">Roles</p>
                @foreach($user->roles as $role)
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 mr-1">{{ ucfirst($role->name) }}</span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
        <h3 class="text-base font-semibold mb-4">Actions</h3>
        <div class="flex gap-3">
            @if($user->isActive())
                <form method="POST" action="{{ route('admin.users.suspend', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700"
                        onclick="return confirm('Suspend this user?')">Suspend User</button>
                </form>
            @else
                <form method="POST" action="{{ route('admin.users.activate', $user) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700">Activate User</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
