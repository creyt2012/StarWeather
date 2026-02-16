<?php

namespace App\Imports;

use App\Models\WeatherMetric;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class WeatherMetricsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new WeatherMetric([
            'station_id' => $row['station_id'] ?? null,
            'latitude' => $row['latitude'],
            'longitude' => $row['longitude'],
            'temperature' => $row['temperature'],
            'humidity' => $row['humidity'],
            'pressure' => $row['pressure'] ?? 1013.25,
            'wind_speed' => ($row['wind_speed'] ?? 0) . ' km/h',
            'wind_direction' => $row['wind_direction'] ?? 'N',
            'cloud_coverage' => $row['cloud_coverage'] ?? 0,
            'source' => 'MISSION_CONTROL_EXCEL',
            'captured_at' => isset($row['timestamp']) ? Carbon::parse($row['timestamp']) : now(),
        ]);
    }
}
