<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Satellite;
use Illuminate\Support\Facades\Http;

class SatelliteUpdateTLE extends Command
{
    protected $signature = 'satellite:update-tle';
    protected $description = 'Update TLE data for all tracked satellites from Celestrack';

    public function handle()
    {
        $this->info('Updating TLE data...');

        // Example URL for weather satellites
        $response = Http::get('https://celestrak.org/NORAD/elements/gp.php?GROUP=weather&FORMAT=tle');

        if ($response->failed()) {
            $this->error('Failed to fetch TLE data from Celestrack.');
            return;
        }

        $lines = explode("\n", $response->body());

        for ($i = 0; $i < count($lines) - 2; $i += 3) {
            $name = trim($lines[$i]);
            $line1 = trim($lines[$i + 1]);
            $line2 = trim($lines[$i + 2]);

            $noradId = trim(substr($line1, 2, 5));

            Satellite::updateOrCreate(
                ['norad_id' => $noradId],
                [
                    'name' => $name,
                    'tle_line1' => $line1,
                    'tle_line2' => $line2,
                    'status' => 'ACTIVE',
                    'type' => 'WEATHER',
                ]
            );
        }

        $this->info('TLE data updated successfully.');
    }
}
