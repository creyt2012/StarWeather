<?php

namespace App\Engines\Analytics;

use App\Models\WeatherMetric;
use App\Models\SatelliteRawData;

class RiskEngine
{
    /**
     * Compute storm risk score (0-100) based on latest fused data.
     */
    public function computeRiskScore(WeatherMetric $current, ?WeatherMetric $previous = null): array
    {
        $weights = [
            'cloud_cover' => 0.25,
            'growth_rate' => 0.30,
            'rain_intensity' => 0.35,
            'anomaly' => 0.10
        ];

        // 1. Cloud Cover Component
        $cloudScore = $current->cloud_coverage;

        // 2. Growth Rate Component
        $growthScore = 0;
        if ($previous) {
            $diff = $current->cloud_coverage - $previous->cloud_coverage;
            // > 20% growth in 30min is critical
            $growthScore = min(100, max(0, ($diff / 20) * 100));
        }

        // 3. Rain Intensity Component
        // Assume 50mm/h is max threshold
        $rainScore = min(100, ($current->rain_intensity / 50) * 100);

        // 4. Anomaly Component (Placeholder for statistical deviation)
        $anomalyScore = 0;

        $finalScore = ($cloudScore * $weights['cloud_cover']) +
            ($growthScore * $weights['growth_rate']) +
            ($rainScore * $weights['rain_intensity']) +
            ($anomalyScore * $weights['anomaly']);

        $level = $this->determineLevel($finalScore);

        return [
            'score' => round($finalScore, 2),
            'level' => $level,
            'components' => [
                'cloud' => $cloudScore,
                'growth' => $growthScore,
                'rain' => $rainScore
            ]
        ];
    }

    private function determineLevel(float $score): string
    {
        if ($score >= 80)
            return 'CRITICAL';
        if ($score >= 60)
            return 'HIGH';
        if ($score >= 30)
            return 'MEDIUM';
        return 'LOW';
    }
}
