<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AlertSettingsController extends Controller
{
    /**
     * Show the alert settings page.
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;

        return Inertia::render('Admin/Alerts/Settings', [
            'settings' => $tenant->settings['notifications'] ?? [
                'channels' => [
                    'telegram' => ['enabled' => false, 'chat_id' => '', 'bot_token' => ''],
                    'slack' => ['enabled' => false, 'webhook_url' => ''],
                    'zalo' => ['enabled' => false, 'oa_id' => '', 'template_id' => ''],
                    'web_push' => ['enabled' => false, 'endpoint' => ''],
                ],
                'thresholds' => [
                    'critical_risk_score' => 80,
                    'min_conjunction_dist' => 50,
                ]
            ]
        ]);
    }

    /**
     * Update notification settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'channels' => 'required|array',
            'thresholds' => 'required|array'
        ]);

        $tenant = Auth::user()->tenant;
        $settings = $tenant->settings ?? [];
        $settings['notifications'] = $request->only(['channels', 'thresholds']);

        $tenant->update(['settings' => $settings]);

        return back()->with('success', 'Notification settings updated successfully.');
    }
}
