<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;

    protected $table = 'users';

    protected $fillable = [
        'email', 'password', 'profile_id', 'role_id', 'is_active'
    ];

    protected $hidden = [
        'password'
    ];

    // JWT Required Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
    public function role()
{
    return $this->belongsTo(Role::class, 'role_id', 'id');
}
}
