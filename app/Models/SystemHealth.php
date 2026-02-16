<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemHealth extends Model
{
    protected $fillable = [
        'service_name',
        'status',
        'latency_ms',
        'metadata',
        'recorded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'latency_ms' => 'float'
    ];
}
