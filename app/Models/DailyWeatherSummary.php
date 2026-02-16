<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyWeatherSummary extends Model
{
    protected $fillable = [
        'date',
        'latitude',
        'longitude',
        'parameter',
        'avg_value',
        'min_value',
        'max_value',
        'sample_count'
    ];

    protected $casts = [
        'date' => 'date',
        'avg_value' => 'float',
        'min_value' => 'float',
        'max_value' => 'float',
        'latitude' => 'float',
        'longitude' => 'float'
    ];
}
