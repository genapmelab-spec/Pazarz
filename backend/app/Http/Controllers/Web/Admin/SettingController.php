<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class SettingController extends Controller
{
    public function edit()
    {
        $settings = [
            'commission_rate' => config('pazarz.commission_rate', 5.0),
            'dispute_window_days' => config('pazarz.dispute_window_days', 7),
            'auto_complete_days' => config('pazarz.auto_complete_days', 14),
        ];

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'dispute_window_days' => 'required|integer|min:1|max:90',
            'auto_complete_days' => 'required|integer|min:1|max:90',
        ]);

        // In production, store in database/config
        // For now, just log the change
        AuditLog::log($request->user(), 'update_platform_settings', null, $validated, $request->ip());

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Platform settings updated.');
    }
}
