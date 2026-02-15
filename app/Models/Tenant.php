<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'plan',
        'settings',
        'expires_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'expires_at' => 'datetime',
    ];

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }
}
