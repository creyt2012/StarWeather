<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemHealth;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemHealthController extends Controller
{
    public function index(): Response
    {
        $services = ['Database', 'Redis', 'API Gateway'];
        $slaData = [];

        foreach ($services as $service) {
            $slaData[$service] = [
                'current' => SystemHealth::where('service_name', $service)->latest('recorded_at')->first(),
                'uptime_24h' => SystemHealth::where('service_name', $service)
                    ->where('recorded_at', '>', now()->subHours(24))
                    ->selectRaw('count(case when status = "operational" then 1 end) * 100.0 / count(*) as uptime')
                    ->value('uptime') ?? 100.0,
                'avg_latency' => SystemHealth::where('service_name', $service)
                    ->where('recorded_at', '>', now()->subHours(24))
                    ->avg('latency_ms') ?? 0,
                'history' => SystemHealth::where('service_name', $service)
                    ->where('recorded_at', '>', now()->subHours(6))
                    ->orderBy('recorded_at', 'asc')
                    ->get(['latency_ms', 'recorded_at', 'status'])
            ];
        }

        return Inertia::render('Admin/System/Health', [
            'sla' => $slaData,
            'recentLogs' => SystemHealth::latest('recorded_at')->limit(20)->get()
        ]);
    }
}
