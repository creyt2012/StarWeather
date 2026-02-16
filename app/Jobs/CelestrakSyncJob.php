<?php

namespace App\Jobs;

use App\Models\Satellite;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CelestrakSyncJob implements ShouldQueue
{
    use Queueable;

    /**
     * Sync TLE data from Celestrak for all active satellites.
     */
    public function handle(): void
    {
        $satellites = Satellite::where('status', 'ACTIVE')->get();

        /** @var Satellite $satellite */
        foreach ($satellites as $satellite) {
            $noradId = $satellite->norad_id;
            if (!$noradId)
                continue;

            try {
                // Fetch TLE from Celestrak GP API
                $response = Http::get("https://celestrak.org/NORAD/elements/gp.php?CATNR={$noradId}&FORMAT=TLE");

                if ($response->successful()) {
                    $lines = explode("\n", trim($response->body()));

                    if (count($lines) >= 3) {
                        // Celestrak TLE format: 0 Name, 1 Line 1, 2 Line 2
                        $satellite->update([
                            'tle_line1' => trim($lines[1]),
                            'tle_line2' => trim($lines[2])
                        ]);
                        Log::info("Synced TLE for {$satellite->name} (NORAD: {$noradId})");
                    }
                } else {
                    Log::warning("Failed to fetch TLE for NORAD {$noradId} from Celestrak.");
                }
            } catch (\Exception $e) {
                Log::error("Celestrak sync error for {$satellite->name}: " . $e->getMessage());
            }

            // Small delay to be polite to Celestrak API
            usleep(500000); // 0.5s
        }
    }
}
