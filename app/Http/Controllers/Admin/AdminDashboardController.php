<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Satellite;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\ActivityLog;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/AdminDashboard', [
            'stats' => [
                'total_satellites' => Satellite::count(),
                'active_satellites' => Satellite::where('status', 'ACTIVE')->count(),
                'total_keys' => ApiKey::count(),
                'active_keys' => ApiKey::where('is_active', true)->count(),
                'total_users' => User::count(),
                'monthly_usage' => ApiKey::sum('usage_count'),
            ],
            'satellite_distribution' => [
                'by_type' => Satellite::selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type'),
                'by_status' => Satellite::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
                'by_priority' => Satellite::selectRaw('priority, count(*) as total')->groupBy('priority')->pluck('total', 'priority'),
            ],
            // Simulated usage trend for the area chart
            'usage_trend' => [
                'labels' => ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00', '23:59'],
                'data' => [12, 45, 67, 89, 76, 54, 32]
            ],
            'recent_keys' => ApiKey::latest()->take(5)->get(),
            'recent_logs' => ActivityLog::with('user')->latest()->take(10)->get(),
            'sla_metrics' => [
                'current' => \App\Models\SystemHealth::where('recorded_at', '>', now()->subMinutes(5))
                    ->latest('recorded_at')
                    ->get()
                    ->groupBy('service_name')
                    ->map(fn($group) => $group->first()),
                'uptime_24h' => \App\Models\SystemHealth::where('recorded_at', '>', now()->subHours(24))
                    ->selectRaw('service_name, count(case when status = "operational" then 1 end) * 100.0 / count(*) as uptime')
                    ->groupBy('service_name')
                    ->pluck('uptime', 'service_name')
            ]
        ]);
    }
}
