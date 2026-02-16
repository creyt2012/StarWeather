<?php

namespace App\Engines\Analytics;

use App\Models\WeatherMetric;
use Carbon\Carbon;

class RiskEngine
{
    /**
     * Compute storm risk score (0-100) based on latest fused data as per Wiki standards.
     * Weights: Cloud Coverage (25%), Density (15%), Rain (30%), Growth (20%), Pressure (10%).
     */
    public function computeRiskScore(WeatherMetric $current, ?WeatherMetric $previous = null): array
    {
        $weights = [
            'cloud_coverage' => 0.25,
            'cloud_density' => 0.15,
            'rain_intensity' => 0.30,
            'cloud_growth' => 0.20,
            'pressure' => 0.10
        ];

        // 1. Cloud Coverage (0-100%)
        $s1 = min(100, $current->cloud_coverage);

        // 2. Cloud Density (Assume 0-100 normalized)
        $s2 = min(100, $current->cloud_density);

        // 3. Rain Intensity (Assume 50mm/h is 100% threshold)
        $s3 = min(100, ($current->rain_intensity / 50) * 100);

        // 4. Cloud Growth Rate (Normalized from % change)
        $s4 = 0;
        if ($previous && $previous->cloud_coverage > 0) {
            $diff = $current->cloud_coverage - $previous->cloud_coverage;
            // > 20% increase in coverage is critical (100 points)
            $s4 = min(100, max(0, ($diff / 20) * 100));
        }

        // 5. Atmospheric Pressure (Normal = 1013.25hPa, Fall < 990hPa is critical)
        // Score = Max(0, 1013.25 - pressure) * normalization
        $s5 = max(0, min(100, (1013.25 - $current->pressure) * 4));

        $finalScore = ($s1 * $weights['cloud_coverage']) +
            ($s2 * $weights['cloud_density']) +
            ($s3 * $weights['rain_intensity']) +
            ($s4 * $weights['cloud_growth']) +
            ($s5 * $weights['pressure']);

        $confidence = $this->calculateConfidence($current, $previous);

        return [
            'score' => round($finalScore, 2),
            'level' => $this->determineLevel($finalScore),
            'confidence' => $confidence,
            'breakdown' => [
                'coverage' => $s1,
                'density' => $s2,
                'rain' => $s3,
                'growth' => $s4,
                'pressure' => $s5
            ]
        ];
    }

    /**
     * Calculate Confidence Score (0-100) based on Data Freshness and Sensor count.
     */
    private function calculateConfidence(WeatherMetric $current, ?WeatherMetric $previous): float
    {
        $score = 100;

        // 1. Freshness Penalty (If data older than 1 hour)
        $ageHours = Carbon::now()->diffInHours($current->captured_at);
        if ($ageHours > 0) {
            $score -= min(50, $ageHours * 10);
        }

        // 2. Provenance Bonus (More sources = more reliable)
        $sources = count($current->data_sources ?? []);
        if ($sources < 2)
            $score -= 20; // Only one source is risky
        if ($sources >= 3)
            $score += 10; // 3+ sources is highly confident

        return min(100, max(0, $score));
    }

    private function determineLevel(float $score): string
    {
        if ($score >= 81)
            return 'CRITICAL';
        if ($score >= 61)
            return 'HIGH';
        if ($score >= 41)
            return 'MEDIUM';
        return 'LOW';
    }
}
