<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('actor')
            ->when($request->actor_id, fn($q, $id) => $q->where('actor_id', $id))
            ->when($request->action, fn($q, $a) => $q->where('action', $a))
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
