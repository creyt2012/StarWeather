<?php

namespace App\Jobs;

use App\Models\WeatherMetric;
use App\Engines\Weather\HimawariService;
use App\Engines\Weather\HimawariProcessor;
use App\Engines\Analytics\RiskEngine;
use App\Events\WeatherMetricsUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HimawariIngestJob implements ShouldQueue
{
    use Queueable;

    public function handle(
        HimawariService $service,
        HimawariProcessor $processor,
        \App\Engines\Weather\AtmosphericModel $atmosphere,
        \App\Engines\Weather\WindEstimator $windEngine,
        RiskEngine $riskEngine,
        \App\Repositories\StateRepository $stateRepo
    ): void {
        try {
            // 1. Fetch latest imagery
            $imagePath = $service->downloadLatest();

            // 2. Process imagery (Crop VN, Get stats)
            $stats = $processor->processImage($imagePath);

            // 3. Derive Physics-based Metrics (Proprietary Engine)
            // Centering on Vietnam coords (Approx 16N, 108E)
            $lat = 16.0;
            $lng = 108.0;

            $temp = $atmosphere->deriveTemperature($stats['mean_brightness'], $lat);
            $pressure = $atmosphere->derivePressure($stats['cloud_coverage'], $lat);
            $wind = $windEngine->estimate($stats, $lat, $lng);

            // 4. Save Metrics
            $metric = WeatherMetric::create([
                'source' => 'Himawari-9',
                'captured_at' => now(),
                'latitude' => $lat,
                'longitude' => $lng,
                'cloud_coverage' => $stats['cloud_coverage'],
                'cloud_density' => $stats['cloud_coverage'] * 0.85, // Direct density proxy
                'rain_intensity' => $stats['rain_estimation'],
                'pressure' => $pressure,
                'data_sources' => ['JMA', 'Himawari-9'],
                'provenance' => [
                    'sensor' => 'AHI',
                    'image_id' => basename($imagePath),
                    'calculations' => 'PROPRIETARY_PHYSICS_V1',
                    'temp_derived' => $temp,
                    'wind_derived' => $wind,
                ]
            ]);

            // 5. Compute Risk & Confidence
            $previous = WeatherMetric::where('id', '!=', $metric->id)->latest('captured_at')->first();
            $risk = $riskEngine->computeRiskScore($metric, $previous);

            // 6. Update Record & L1 Cache
            $metric->update([
                'risk_score' => $risk['score'],
                'risk_level' => $risk['level'],
                'confidence_score' => $risk['confidence'],
                'cloud_growth_rate' => $risk['breakdown']['growth']
            ]);

            $stateRepo->setLatestWeather($metric);

            event(new WeatherMetricsUpdated($metric, $risk));

            Log::info("Enterprise Ingestion Complete. Risk: {$risk['score']}, Conf: {$risk['confidence']}");

        } catch (\Exception $e) {
            Log::error("Himawari Ingestion Failed: " . $e->getMessage());
        }
    }
}
