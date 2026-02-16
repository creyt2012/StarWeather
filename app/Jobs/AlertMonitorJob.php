<?php

namespace App\Jobs;

use App\Models\WeatherMetric;
use App\Services\Notifications\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AlertMonitorJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job to monitor system vitals and trigger alerts.
     */
    public function handle(NotificationService $notificationService): void
    {
        // 1. Weather Anomalies (Higher than 80 risk score)
        $threats = WeatherMetric::where('risk_score', '>', 80)
            ->where('captured_at', '>', now()->subMinutes(5))
            ->get();

        foreach ($threats as $threat) {
            $notificationService->broadcastAlert(
                'METEOROLOGICAL_THREAT',
                "Extreme event at {$threat->latitude}, {$threat->longitude}. Risk Score: {$threat->risk_score}",
                'CRITICAL'
            );
        }

        // 2. Mission Critical Notifications (Simulated example)
        // Log::info("Alert Monitor Scan Complete.");
    }
}
