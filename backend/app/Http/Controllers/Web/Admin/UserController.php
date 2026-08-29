<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('seller')
            ->when($request->role, fn($q, $r) => $q->where('role_id', $r))
            ->when($request->search, fn($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'LIKE', "%{$s}%")
                  ->orWhere('email', 'LIKE', "%{$s}%");
            }))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['seller.store', 'orders' => fn($q) => $q->latest()->limit(10)]);

        return view('admin.users.show', compact('user'));
    }

    public function suspend(Request $request, User $user)
    {
        $user->update(['status' => 'suspended']);

        AuditLog::log($request->user(), 'suspend_user', $user, [
            'status' => ['old' => 'active', 'new' => 'suspended'],
        ], $request->ip());

        return redirect()->back()->with('success', 'User suspended.');
    }

    public function activate(Request $request, User $user)
    {
        $user->update(['status' => 'active']);

        AuditLog::log($request->user(), 'activate_user', $user, [
            'status' => ['old' => 'suspended', 'new' => 'active'],
        ], $request->ip());

        return redirect()->back()->with('success', 'User activated.');
    }
}
