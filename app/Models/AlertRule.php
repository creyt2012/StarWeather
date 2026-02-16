<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertRule extends Model
{
    protected $fillable = [
        'name',
        'parameter',
        'operator',
        'threshold',
        'severity',
        'is_active',
        'channels',
        'metadata'
    ];

    protected $casts = [
        'channels' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'threshold' => 'float'
    ];
}
