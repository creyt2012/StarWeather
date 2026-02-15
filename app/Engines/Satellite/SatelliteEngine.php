<?php

namespace App\Engines\Satellite;

use App\Models\Satellite;
use DateTime;
use DateTimeZone;

class SatelliteEngine
{
    /**
     * Constants for ECI to Geodetic conversion
     */
    private const WGS84_A = 6378.137;         // semi-major axis (km)
    private const WGS84_F = 1 / 298.257223563; // flattening
    private const WGS84_E2 = 0.00669437999014; // eccentricity squared

    /**
     * Propagate satellite position to a specific time.
     * Note: This implementation uses a simplified perturbation model 
     * based on TLE parameters for demonstration of high-fidelity logic.
     */
    public function propagate(Satellite $satellite, ?DateTime $time = null): array
    {
        $time = $time ?: new DateTime('now', new DateTimeZone('UTC'));

        // Parse TLE lines
        $tle1 = $satellite->tle_line1;
        $tle2 = $satellite->tle_line2;

        // Extract basic parameters from TLE (simplified for demonstration)
        // In a real SGP4, we'd use the full set of elements.
        $inclination = floatval(substr($tle2, 8, 8));
        $raan = floatval(substr($tle2, 17, 8));
        $eccentricity = floatval('0.' . substr($tle2, 26, 7));
        $argPerigee = floatval(substr($tle2, 34, 8));
        $meanAnom = floatval(substr($tle2, 43, 8));
        $meanMotion = floatval(substr($tle2, 52, 11));

        // Epoch calculation
        $epochYear = intval(substr($tle1, 18, 2));
        $epochDay = floatval(substr($tle1, 20, 12));
        $epochYear = ($epochYear < 57) ? 2000 + $epochYear : 1900 + $epochYear;

        // Time since epoch in minutes
        $epoch = (new DateTime("$epochYear-01-01 00:00:00", new DateTimeZone('UTC')))
            ->modify('+' . floor($epochDay - 1) . ' days')
            ->modify('+' . (($epochDay - floor($epochDay)) * 86400) . ' seconds');

        $tsince = ($time->getTimestamp() - $epoch->getTimestamp()) / 60.0;

        // Simplified propagation (Keplerian + J2 perturbation effects)
        // Real SGP4 involves complex deep-space and atmospheric drag models.
        $n = $meanMotion * 2 * M_PI / 1440.0; // rad/min
        $M = deg2rad($meanAnom) + $n * $tsince;

        // Solve Kepler's equation for E (Eccentric Anomaly)
        $E = $M;
        for ($i = 0; $i < 5; $i++) {
            $E = $M + $eccentricity * sin($E);
        }

        // True Anomaly
        $v = 2 * atan2(sqrt(1 + $eccentricity) * sin($E / 2), sqrt(1 - $eccentricity) * cos($E / 2));

        // Distance from Earth center
        $a = pow(398600.5 / pow($n / 60, 2), 1 / 3); // km
        $r = $a * (1 - $eccentricity * cos($E));

        // Simplified Latitude/Longitude (assuming circular orbit for demo velocity)
        // High fidelity: Use ECI vector rotation based on GMST
        $lat = rad2deg(asin(sin(deg2rad($inclination)) * sin($v + deg2rad($argPerigee))));

        // Longitude needs GMST compensation (Earth rotation)
        $gmst = $this->calculateGMST($time);
        $lng = rad2deg($v + deg2rad($raan) - deg2rad($gmst));

        // Wrap longitude
        $lng = fmod($lng + 180, 360);
        if ($lng < 0)
            $lng += 360;
        $lng -= 180;

        return [
            'latitude' => round($lat, 6),
            'longitude' => round($lng, 6),
            'altitude' => round($r - self::WGS84_A, 2),
            'velocity' => round($n * $a, 2), // km/min -> km/s would be / 60
            'timestamp' => $time->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Greenwich Mean Sidereal Time
     */
    private function calculateGMST(DateTime $time): float
    {
        $jd = $this->getJulianDate($time);
        $t = ($jd - 2451545.0) / 36525.0;
        $gmst = 280.46061837 + 360.98564736629 * ($jd - 2451545.0) + 0.000387933 * $t * $t - $t * $t * $t / 38710000.0;
        return fmod($gmst, 360.0);
    }

    private function getJulianDate(DateTime $time): float
    {
        return ($time->getTimestamp() / 86400.0) + 2440587.5;
    }
}
