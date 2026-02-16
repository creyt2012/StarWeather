<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conjunction extends Model
{
    protected $fillable = [
        'satellite_a_id',
        'satellite_b_id',
        'distance',
        'probability',
        'tca',
        'status',
        'metadata'
    ];

    protected $casts = [
        'tca' => 'datetime',
        'metadata' => 'array'
    ];

    public function satelliteA()
    {
        return $this->belongsTo(Satellite::class, 'satellite_a_id');
    }

    public function satelliteB()
    {
        return $this->belongsTo(Satellite::class, 'satellite_b_id');
    }
}
