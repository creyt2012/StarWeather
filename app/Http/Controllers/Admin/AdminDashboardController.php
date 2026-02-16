<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Satellite;
use App\Models\ApiKey;
use App\Models\User;
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
                'data' => [12, 45, 67, 89, 45, 90, 120]
            ],
            'recent_keys' => ApiKey::latest()->take(5)->get(),
        ]);
    }
}
