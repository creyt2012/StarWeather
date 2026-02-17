<?php

namespace App\Services\Intelligence;

class AviationIntelService
{
    /**
     * Get global aviation traffic data.
     */
    public function getGlobalTraffic(): array
    {
        $airlines = ['Emirates', 'Vietjet Air', 'Lufthansa', 'Delta Airlines', 'Singapore Airlines', 'Qatar Airways'];
        $vessels = [];

        for ($i = 0; $i < 150; $i++) {
            $vessels[] = [
                'id' => 'FL' . rand(1000, 9999),
                'name' => 'FLIGHT_' . rand(100, 999),
                'airline' => $airlines[array_rand($airlines)],
                'latitude' => (mt_rand(-7000, 7000) / 100),
                'longitude' => (mt_rand(-18000, 18000) / 100),
                'altitude' => rand(28000, 42000), // ft
                'velocity' => rand(800, 950), // km/h
                'heading' => rand(0, 359),
                'status' => 'EN_ROUTE',
                'strategic_priority' => (rand(0, 10) > 8) ? 'HIGH' : 'NORMAL'
            ];
        }

        return $vessels;
    }

    /**
     * Detect clear-air turbulence (CAT) zones.
     */
    public function getTurbulenceZones(): array
    {
        $zones = [];
        for ($i = 0; $i < 10; $i++) {
            $zones[] = [
                'latitude' => (mt_rand(-6000, 6000) / 100),
                'longitude' => (mt_rand(-17000, 17000) / 100),
                'radius' => rand(2, 8),
                'severity' => rand(1, 5) // 5 is extreme
            ];
        }
        return $zones;
    }
}
