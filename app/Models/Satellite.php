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
    ];

    public function tracks()
    {
        return $this->hasMany(SatelliteTrack::class);
    }
}
