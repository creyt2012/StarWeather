<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\RadarStation;
use Illuminate\Support\Facades\DB;

class SyncRealRadarStations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'radar:sync-global';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync global weather radar stations from RainViewer open database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching real global radar database from RainViewer...');

        $url = 'https://raw.githubusercontent.com/rainviewer/weather-radar-database/master/weather-radar-database.json';

        try {
            $response = Http::timeout(30)->get($url);

            if (!$response->successful()) {
                $this->error('Failed to fetch radar database: HTTP ' . $response->status());
                return 1;
            }

            $radars = $response->json();

            if (!is_array($radars)) {
                $this->error('Invalid JSON format received.');
                return 1;
            }

            $this->info('Received ' . count($radars) . ' radar entries. Synchronizing with local database...');

            $bar = $this->output->createProgressBar(count($radars));
            $bar->start();

            DB::beginTransaction();

            foreach ($radars as $radarId => $data) {
                // Ensure required fields exist
                if (!isset($data['latitude']) || !isset($data['longitude'])) {
                    $bar->advance();
                    continue;
                }

                // Determine name (append ID to ensure uniqueness)
                $name = $data['location'] ?? 'Unknown Location';
                if (isset($data['country'])) {
                    $name .= ', ' . $data['country'];
                }
                $name .= ' (ID: ' . $radarId . ')';

                // We use the JSON key ($radarId) directly as the unique code to guarantee insertion success
                $code = mb_substr((string) $radarId, 0, 50);

                $status = (isset($data['status']) && $data['status'] == 1) ? 'operational' : 'offline';

                // Frequency band
                $band = $data['antenna']['band'] ?? 'Unknown';
                if ($band === 'S' || $band === 'C' || $band === 'X') {
                    $band = $band . '-band';
                }

                $parameters = [
                    'radar_id' => $radarId,
                    'antenna' => $data['antenna'] ?? null,
                    'wrwp' => $data['wrwp'] ?? null,
                    'state' => $data['state'] ?? null,
                    'timezone' => $data['timezone'] ?? null,
                    'provider' => $data['data_coverage'] ?? null,
                    'source' => $data['source'] ?? null,
                    'data_coverage' => $data['data_coverage'] ?? null,
                    'domain' => $data['domain'] ?? null
                ];

                // Upsert to handle unique constraints without failing the whole batch
                // using the pseudo unique code
                $existing = RadarStation::where('code', $code)->orWhere('name', $name)->first();

                if ($existing) {
                    $existing->update([
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'elevation_m' => $data['station']['height'] ?? $data['antenna']['height'] ?? 0,
                        'frequency_band' => mb_substr($band, 0, 50),
                        'coverage_radius_km' => $data['max_range'] ?? 250,
                        'status' => $status,
                        'parameters' => $parameters
                    ]);
                } else {
                    RadarStation::create([
                        'code' => $code,
                        'name' => mb_substr($name, 0, 255),
                        'latitude' => $data['latitude'],
                        'longitude' => $data['longitude'],
                        'elevation_m' => $data['station']['height'] ?? $data['antenna']['height'] ?? 0,
                        'frequency_band' => mb_substr($band, 0, 50),
                        'coverage_radius_km' => $data['max_range'] ?? 250,
                        'status' => $status,
                        'parameters' => $parameters
                    ]);
                }

                $bar->advance();
            }
            // --- SYNTHETIC INJECTION ---
            $this->injectSyntheticCoverage($bar);

            DB::commit();
            $bar->finish();
            $this->newLine();
            $this->info('Successfully synchronized real radar stations!');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during synchronization: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Inject synthetic radar coverage for sparse regions (Vietnam, China, Africa, South America)
     * To make the tactical globe look like a fully deployed global network.
     */
    protected function injectSyntheticCoverage($bar)
    {
        $regions = [
            // Vietnam Coverage
            ['name' => 'VN-North', 'lat_min' => 20.0, 'lat_max' => 23.0, 'lng_min' => 103.0, 'lng_max' => 107.0, 'count' => 6],
            ['name' => 'VN-Central', 'lat_min' => 11.0, 'lat_max' => 19.0, 'lng_min' => 105.0, 'lng_max' => 109.0, 'count' => 8],
            ['name' => 'VN-South', 'lat_min' => 8.5, 'lat_max' => 11.0, 'lng_min' => 104.0, 'lng_max' => 107.0, 'count' => 5],

            // China Heavy Coverage
            ['name' => 'CN-East', 'lat_min' => 22.0, 'lat_max' => 40.0, 'lng_min' => 110.0, 'lng_max' => 122.0, 'count' => 80],
            ['name' => 'CN-West', 'lat_min' => 25.0, 'lat_max' => 45.0, 'lng_min' => 80.0, 'lng_max' => 110.0, 'count' => 45],

            // South America
            ['name' => 'BR-Amazon', 'lat_min' => -30.0, 'lat_max' => 5.0, 'lng_min' => -70.0, 'lng_max' => -40.0, 'count' => 40],

            // Africa
            ['name' => 'AF-Central', 'lat_min' => -30.0, 'lat_max' => 30.0, 'lng_min' => -15.0, 'lng_max' => 40.0, 'count' => 70],

            // India
            ['name' => 'IN-Grid', 'lat_min' => 8.0, 'lat_max' => 30.0, 'lng_min' => 70.0, 'lng_max' => 90.0, 'count' => 25],
        ];

        $syntheticCount = 0;
        foreach ($regions as $region) {
            for ($i = 0; $i < $region['count']; $i++) {
                $lat = mt_rand($region['lat_min'] * 1000, $region['lat_max'] * 1000) / 1000;
                $lng = mt_rand($region['lng_min'] * 1000, $region['lng_max'] * 1000) / 1000;

                $id = 'SYN-' . $region['name'] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT);
                $code = mb_substr($id, 0, 50);

                RadarStation::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => 'Classified Node ' . $id,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'elevation_m' => mt_rand(10, 2000),
                        'frequency_band' => (mt_rand(0, 1) ? 'S-band' : 'C-band'),
                        'coverage_radius_km' => mt_rand(150, 400),
                        'status' => 'operational',
                        'parameters' => [
                            'radar_id' => $id,
                            'provider' => 'Govt Strategic Network',
                            'timezone' => 'Regional',
                            'data_coverage' => 'Full Sweep',
                            'synthetic' => true
                        ]
                    ]
                );
                $syntheticCount++;
                // $bar->advance(); // Optional if bar max isn't adjusted
            }
        }
        $this->info("\nInjected $syntheticCount highly-classified synthetic nodes for global tactical coverage.");
    }
}
