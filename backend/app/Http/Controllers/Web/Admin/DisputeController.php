<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\DisputeMessage;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request)
    {
        $disputes = Dispute::with(['subOrder.order.user', 'subOrder.store', 'raiser'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.disputes.index', compact('disputes'));
    }

    public function show(Dispute $dispute)
    {
        $dispute->load([
            'subOrder.order',
            'subOrder.store',
            'subOrder.items.variant.product',
            'raiser',
            'resolver',
            'messages.sender',
        ]);

        return view('admin.disputes.show', compact('dispute'));
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'resolution' => 'required|string|max:1000',
        ]);

        $dispute->update([
            'status' => 'resolved',
            'resolution' => $validated['resolution'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        AuditLog::log($request->user(), 'resolve_dispute', $dispute, [
            'status' => ['old' => $dispute->status, 'new' => 'resolved'],
        ], $request->ip());

        return redirect()->back()->with('success', 'Dispute resolved.');
    }

    public function reject(Request $request, Dispute $dispute)
    {
        $dispute->update([
            'status' => 'rejected',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        AuditLog::log($request->user(), 'reject_dispute', $dispute, [
            'status' => ['old' => $dispute->status, 'new' => 'rejected'],
        ], $request->ip());

        return redirect()->back()->with('success', 'Dispute rejected.');
    }

    public function sendMessage(Request $request, Dispute $dispute)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        DisputeMessage::create([
            'dispute_id' => $dispute->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        $dispute->update(['status' => 'in_review']);

        return redirect()->back()->with('success', 'Message sent.');
    }
}
