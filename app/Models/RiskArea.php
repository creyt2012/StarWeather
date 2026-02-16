<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskArea extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'severity',
        'geometry',
        'metadata',
        'is_active'
    ];

    protected $casts = [
        'geometry' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean'
    ];
}
