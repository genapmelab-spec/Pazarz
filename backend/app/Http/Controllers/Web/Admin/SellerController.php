<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $sellers = Seller::with(['user', 'store'])
            ->when($request->status, fn($q, $s) => $q->where('verification_status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.sellers.index', compact('sellers'));
    }

    public function show(Seller $seller)
    {
        $seller->load(['user', 'store', 'store.products', 'store.address']);

        return view('admin.sellers.show', compact('seller'));
    }

    public function approve(Request $request, Seller $seller)
    {
        $seller->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        AuditLog::log($request->user(), 'approve_seller', $seller, [
            'verification_status' => ['old' => 'pending', 'new' => 'verified'],
        ], $request->ip());

        // Notify seller
        \App\Models\Notification::createForUser(
            $seller->user,
            'seller_approved',
            'Seller Account Approved',
            'Your seller account has been verified. You can now start selling!',
        );

        return redirect()->back()->with('success', 'Seller approved.');
    }

    public function reject(Request $request, Seller $seller)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $seller->update(['verification_status' => 'rejected']);

        AuditLog::log($request->user(), 'reject_seller', $seller, [
            'verification_status' => ['old' => 'pending', 'new' => 'rejected'],
            'reason' => $validated['reason'],
        ], $request->ip());

        // Notify seller
        \App\Models\Notification::createForUser(
            $seller->user,
            'seller_rejected',
            'Seller Account Rejected',
            "Your seller account was not approved. Reason: {$validated['reason']}",
        );

        return redirect()->back()->with('success', 'Seller rejected.');
    }
}
