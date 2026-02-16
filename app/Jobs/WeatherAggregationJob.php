<?php

namespace App\Jobs;

use App\Models\DailyWeatherSummary;
use App\Models\WeatherMetric;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class WeatherAggregationJob implements ShouldQueue
{
    use Queueable;

    protected ?Carbon $date;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $date = null)
    {
        $this->date = $date ?: now()->subDay();
    }

    /**
     * Aggregate daily weather metrics into summaries.
     */
    public function handle(): void
    {
        $startOfDay = $this->date->copy()->startOfDay();
        $endOfDay = $this->date->copy()->endOfDay();

        // Parameters to aggregate
        $params = ['temperature', 'humidity', 'pressure', 'rain_intensity', 'wind_speed'];

        // Fetch unique coordinates with data on this date
        $stations = WeatherMetric::whereBetween('captured_at', [$startOfDay, $endOfDay])
            ->select('latitude', 'longitude')
            ->distinct()
            ->get();

        foreach ($stations as $station) {
            foreach ($params as $param) {
                // Get aggregate values for the parameter
                $stats = WeatherMetric::whereBetween('captured_at', [$startOfDay, $endOfDay])
                    ->where('latitude', $station->latitude)
                    ->where('longitude', $station->longitude)
                    ->selectRaw("
                        AVG($param) as avg_val, 
                        MIN($param) as min_val, 
                        MAX($param) as max_val, 
                        COUNT($param) as sample_count
                    ")
                    ->first();

                if ($stats && $stats->sample_count > 0) {
                    DailyWeatherSummary::updateOrCreate(
                        [
                            'date' => $startOfDay->toDateString(),
                            'latitude' => $station->latitude,
                            'longitude' => $station->longitude,
                            'parameter' => $param
                        ],
                        [
                            'avg_value' => $stats->avg_val,
                            'min_value' => $stats->min_val,
                            'max_value' => $stats->max_val,
                            'sample_count' => $stats->sample_count
                        ]
                    );
                }
            }
        }
    }
}
