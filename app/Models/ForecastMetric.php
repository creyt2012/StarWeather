<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastMetric extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'forecast_time',
        'parameter',
        'value',
        'model',
        'metadata'
    ];

    protected $casts = [
        'forecast_time' => 'datetime',
        'metadata' => 'array',
        'value' => 'float',
        'latitude' => 'float',
        'longitude' => 'float'
    ];
}
