<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    const ROLE_MISSION_OPERATOR = 'MISSION_OPERATOR';
    const ROLE_INTEL_SPECTATOR = 'INTEL_SPECTATOR';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function isAdmin()
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isOperator()
    {
        return in_array($this->role, [self::ROLE_SUPER_ADMIN, self::ROLE_MISSION_OPERATOR]);
    }

    public function isSpectator()
    {
        return $this->role === self::ROLE_INTEL_SPECTATOR;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
