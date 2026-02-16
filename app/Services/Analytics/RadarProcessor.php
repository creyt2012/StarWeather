<?php

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Log;

class RadarProcessor
{
    /**
     * Marshall-Palmer coefficients for stratiform rain.
     * Z = a * R^b
     */
    private const COEFF_A = 200.0;
    private const COEFF_B = 1.6;

    /**
     * Convert Radar Reflectivity (dBZ) to Rainfall Rate (mm/h).
     * 
     * Formula:
     * 1. Z = 10^(dBZ / 10)
     * 2. R = (Z / a)^(1 / b)
     */
    public function dbzToRainfall(float $dbz): float
    {
        if ($dbz <= 0) {
            return 0.0;
        }

        // Convert dBZ to Z (reflectivity factor in mm^6/m^3)
        $z = pow(10, ($dbz / 10.0));

        // Convert Z to R (rainfall rate in mm/h)
        $r = pow(($z / self::COEFF_A), (1.0 / self::COEFF_B));

        return round($r, 2);
    }

    /**
     * Process a batch of radar data points.
     * Expects array of ['lat', 'lng', 'dbz']
     */
    public function processBatch(array $points): array
    {
        return array_map(function ($point) {
            return [
                'latitude' => $point['latitude'],
                'longitude' => $point['longitude'],
                'intensity' => $this->dbzToRainfall($point['dbz']),
                'dbz' => $point['dbz']
            ];
        }, $points);
    }

    /**
     * Get descriptive risk level based on dBZ.
     */
    public function getRiskLevel(float $dbz): string
    {
        if ($dbz < 20)
            return 'CLEAR';
        if ($dbz < 30)
            return 'LIGHT_RAIN';
        if ($dbz < 45)
            return 'MODERATE';
        if ($dbz < 55)
            return 'HEAVY';
        return 'EXTREME_STORM';
    }
}
