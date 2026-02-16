<?php

namespace App\Jobs;

use App\Services\Analytics\RadarProcessor;
use App\Events\RadarFrameUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RadarIngestJob implements ShouldQueue
{
    use Queueable;

    /**
     * Periodically ingest or simulate radar reflectivity data.
     */
    public function handle(RadarProcessor $processor): void
    {
        // 1. Simulate Raw Radar Data (dBZ)
        // In production, this would fetch from a NetCDF or BUFR source
        $points = $this->simulateRadarData();

        // 2. Process Batch (Z-R Conversion)
        $processedData = $processor->processBatch($points);

        // 3. Cache for Fast Frontend Access
        Redis::set('radar_current_frame', json_encode($processedData));
        Redis::expire('radar_current_frame', 60);

        // 4. Global Broadcast via Laravel Reverb
        event(new \App\Events\RadarFrameUpdated($processedData));

        // Log::info("Radar Frame Processed: " . count($processedData) . " points.");
    }

    private function simulateRadarData(): array
    {
        $points = [];
        // Simulate a simple storm front moving around a central point
        $baseLat = 21.0285; // Hanoi Area
        $baseLng = 105.8542;
        $count = 50; // Points per frame for performance

        for ($i = 0; $i < $count; $i++) {
            $latOffset = (mt_rand(-100, 100) / 100.0) * 1.5;
            $lngOffset = (mt_rand(-100, 100) / 100.0) * 1.5;

            $points[] = [
                'latitude' => $baseLat + $latOffset,
                'longitude' => $baseLng + $lngOffset,
                'dbz' => mt_rand(5, 60) // Reflectivity between 5 and 60 dBZ
            ];
        }

        return $points;
    }
}
