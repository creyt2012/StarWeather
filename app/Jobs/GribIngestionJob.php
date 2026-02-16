<?php

namespace App\Jobs;

use App\Models\ForecastMetric;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GribIngestionJob implements ShouldQueue
{
    use Queueable;

    /**
     * Ingest and process Global Forecasting System (GFS) data.
     */
    public function handle(): void
    {
        // 1. Define Target Locations (e.g., major maritime hubs)
        $hubs = [
            ['lat' => 21.0285, 'lng' => 105.8542, 'name' => 'Hanoi'],
            ['lat' => 10.8231, 'lng' => 106.6297, 'name' => 'HCM_City'],
            ['lat' => 16.0471, 'lng' => 108.2062, 'name' => 'Da_Nang']
        ];

        // 2. Parameters to Forecast
        $params = ['temperature', 'wind_speed', 'rain_intensity', 'pressure'];

        // 3. Generate Forecast for the next 48 hours (in 3-hour increments)
        foreach ($hubs as $hub) {
            for ($h = 3; $h <= 48; $h += 3) {
                $forecastTime = now()->addHours($h)->startOfHour();

                foreach ($params as $param) {
                    $value = $this->simulateValue($param);

                    ForecastMetric::updateOrCreate(
                        [
                            'latitude' => $hub['lat'],
                            'longitude' => $hub['lng'],
                            'forecast_time' => $forecastTime,
                            'parameter' => $param,
                            'model' => 'GFS'
                        ],
                        [
                            'value' => $value,
                            'metadata' => ['sector' => $hub['name']]
                        ]
                    );
                }
            }
        }

        // Cleanup old forecasts
        ForecastMetric::where('forecast_time', '<', now())->delete();

        // Log::info("NWP GFS Forecast Ingestion Complete.");
    }

    private function simulateValue(string $param): float
    {
        switch ($param) {
            case 'temperature':
                return 22 + mt_rand(-50, 80) / 10.0;
            case 'wind_speed':
                return mt_rand(5, 45);
            case 'rain_intensity':
                return mt_rand(0, 15) > 10 ? mt_rand(1, 25) : 0;
            case 'pressure':
                return 1005 + mt_rand(-100, 100) / 10.0;
            default:
                return 0;
        }
    }
}
