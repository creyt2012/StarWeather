<?php

namespace App\Jobs;

use App\Models\GroundStation;
use App\Models\WeatherMetric;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GroundIngestJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job to ingest or simulate data from ground stations.
     */
    public function handle(): void
    {
        $stations = GroundStation::where('status', 'ONLINE')->get();

        foreach ($stations as $station) {
            // Rough simulation based on latitude
            $tempBase = $station->latitude > 15 ? 20 : 28;

            WeatherMetric::create([
                'station_id' => $station->id,
                'latitude' => $station->latitude,
                'longitude' => $station->longitude,
                'temperature' => round($tempBase + (rand(-100, 100) / 10), 1),
                'humidity' => rand(40, 95),
                'pressure' => round(1013 + (rand(-200, 200) / 10), 1),
                'cloud_coverage' => rand(0, 100),
                'rain_intensity' => rand(0, 10) > 8 ? rand(1, 50) : 0,
                'risk_score' => rand(0, 100),
                'risk_level' => 'LOW',
                'wind_speed' => rand(0, 40) . ' km/h',
                'wind_direction' => ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'][rand(0, 7)],
                'source' => 'GROUND_SENSOR_SIM',
                'captured_at' => now(),
            ]);
        }
    }
}
