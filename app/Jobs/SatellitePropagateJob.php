<?php

namespace App\Jobs;

use App\Engines\Satellite\SatelliteEngine;
use App\Models\Satellite;
use App\Models\SatelliteTrack;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SatellitePropagateJob implements ShouldQueue
{
    use Queueable;

    public function handle(SatelliteEngine $engine): void
    {
        try {
            Log::info('Starting Satellite Propagation...');

            $satellites = Satellite::where('status', 'ACTIVE')->get();

            foreach ($satellites as $satellite) {
                if ($satellite->tle_line1 && $satellite->tle_line2) {
                    $coords = $engine->propagate($satellite);

                    SatelliteTrack::create([
                        'satellite_id' => $satellite->id,
                        'latitude' => $coords['latitude'],
                        'longitude' => $coords['longitude'],
                        'altitude' => $coords['altitude'],
                        'velocity' => $coords['velocity'],
                        'captured_at' => now(),
                    ]);
                }
            }

            Log::info('Satellite Propagation successful.');
        } catch (\Exception $e) {
            Log::error('Satellite Propagation failed: ' . $e->getMessage());
        }
    }
}
