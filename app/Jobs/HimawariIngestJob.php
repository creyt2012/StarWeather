<?php

namespace App\Jobs;

use App\Engines\Weather\HimawariService;
use App\Engines\Weather\HimawariProcessor;
use App\Models\WeatherMetric;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HimawariIngestJob implements ShouldQueue
{
    use Queueable;

    public function handle(HimawariService $service, HimawariProcessor $processor): void
    {
        try {
            Log::info('Starting Himawari Ingestion pipeline...');

            $metadata = $service->fetchLatestMetadata();
            // In a real scenario, we would loop through tiles and merge them.
            // Here we simulate tile (0,0,0) as basic input.
            $tilePath = $service->downloadTile($metadata['timestamp'], 0, 0, 0);

            $coverage = $processor->calculateCloudCoverage($tilePath);

            WeatherMetric::create([
                'latitude' => 15.8, // Center of Vietnam approx
                'longitude' => 108.3,
                'cloud_coverage' => $coverage,
                'timestamp' => now(),
                'data_sources' => ['Himawari-9'],
            ]);

            Log::info("Himawari Ingestion successful. Coverage: {$coverage}%");
        } catch (\Exception $e) {
            Log::error("Himawari Ingestion failed: " . $e->getMessage());
        }
    }
}
