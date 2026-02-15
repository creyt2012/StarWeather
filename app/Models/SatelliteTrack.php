<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatelliteTrack extends Model
{
    use HasFactory;

    protected $fillable = [
        'satellite_id',
        'latitude',
        'longitude',
        'altitude',
        'velocity',
        'visibility_radius',
        'captured_at',
    ];

    public function satellite()
    {
        return $this->belongsTo(Satellite::class);
    }
}
