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
        'rain_intensity',
        'risk_score',
        'risk_level',
        'source',
        'captured_at',
        'data_sources',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'data_sources' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];
}
