<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'latitude',
        'longitude',
        'cloud_coverage',
        'cloud_density',
        'rain_intensity',
        'temperature',
        'humidity',
        'pressure',
        'risk_score',
        'risk_level',
        'confidence',
        'timestamp',
        'data_sources',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'data_sources' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
