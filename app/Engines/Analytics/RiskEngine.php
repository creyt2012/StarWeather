<?php

namespace App\Engines\Analytics;

use App\Models\WeatherMetric;

class RiskEngine
{
    /**
     * Calculate risk score (0-100) based on weather metrics.
     */
    public function calculateScore(WeatherMetric $metric): float
    {
        $score = 0;

        // 1. Cloud Coverage Weight (30%)
        $score += ($metric->cloud_coverage ?? 0) * 0.3;

        // 2. Rain Intensity Weight (40%)
        // Assume 50mm/h is max risk (100 points for this segment)
        $rainWeight = min(($metric->rain_intensity ?? 0) / 50, 1) * 40;
        $score += $rainWeight;

        // 3. Pressure Anomaly (30%)
        // Placeholder: If pressure < 1000 hPa, increase risk
        if ($metric->pressure && $metric->pressure < 1000) {
            $score += 30;
        }

        return min($score, 100);
    }

    /**
     * Terminate risk level from score.
     */
    public function getLevel(float $score): string
    {
        if ($score > 80)
            return 'CRITICAL';
        if ($score > 60)
            return 'HIGH';
        if ($score > 40)
            return 'MEDIUM';
        return 'LOW';
    }
}
