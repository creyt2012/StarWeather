<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satellite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'norad_id',
        'tle_line1',
        'tle_line2',
        'type',
        'status',
        'api_config',
        'data_source',
        'source_url',
        'dataset_name',
        'priority',
    ];

    protected $casts = [
        'api_config' => 'array',
    ];

    public function tracks()
    {
        return $this->hasMany(SatelliteTrack::class);
    }

    /**
     * Get live orbital telemetry for the satellite.
     * 
     * @return array
     */
    public function getTelemetryAttribute()
    {
        return app(\App\Engines\Satellite\SatelliteEngine::class)->propagate($this);
    }
}
