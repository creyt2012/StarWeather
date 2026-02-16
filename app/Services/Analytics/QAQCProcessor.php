<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Log;

class QAQCProcessor
{
    /**
     * Validate and clean weather data.
     */
    /**
     * Spatial Consistency Check: Compare with neighboring stations.
     */
    public function validateSpatialConsistency(array $data, array $neighbors): array
    {
        if (empty($neighbors)) {
            return array_merge($data, ['spatial_check' => 'skipped']);
        }

        $params = ['temperature', 'pressure', 'humidity'];
        $flags = $data['qa_flags'] ?? [];
        $quality = $data['quality_score'] ?? 100;

        foreach ($params as $param) {
            if (!isset($data[$param]))
                continue;

            $neighborValues = collect($neighbors)->pluck($param)->filter()->all();
            if (empty($neighborValues))
                continue;

            $average = array_sum($neighborValues) / count($neighborValues);
            $deviation = abs($data[$param] - $average);

            // Enterprise Thresholds:
            // Temp > 5°C diff from neighbors = suspicious
            // Pressure > 3hPa diff = suspicious
            $threshold = ($param === 'temperature') ? 5 : (($param === 'pressure') ? 3 : 20);

            if ($deviation > $threshold) {
                $quality -= 15;
                $flags[] = "SPATIAL_ANOMALY_" . strtoupper($param);
            }
        }

        return array_merge($data, [
            'quality_score' => max(0, $quality),
            'qa_flags' => array_unique($flags),
            'is_valid' => $quality >= 60,
            'spatial_check' => 'completed'
        ]);
    }

    public function process(array $data): array
    {
        $quality = 100;
        $flags = [];

        // 1. Physical Range Checks
        if ($data['temperature'] < -80 || $data['temperature'] > 60) {
            $quality -= 40;
            $flags[] = 'TEMP_OUT_OF_RANGE';
        }

        if ($data['humidity'] < 0 || $data['humidity'] > 100) {
            $quality -= 30;
            $flags[] = 'HUMIDITY_INVALID';
        }

        if ($data['pressure'] < 800 || $data['pressure'] > 1100) {
            $quality -= 30;
            $flags[] = 'PRESSURE_ANOMALY';
        }

        // 2. Logic Check (e.g., Rain without high humidity)
        if ($data['rain_intensity'] > 0 && $data['humidity'] < 30) {
            $quality -= 20;
            $flags[] = 'RAIN_HUMIDITY_MISMATCH';
        }

        return array_merge($data, [
            'quality_score' => max(0, $quality),
            'qa_flags' => $flags,
            'is_valid' => $quality >= 60
        ]);
    }
}
