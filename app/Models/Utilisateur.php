<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Utilisateur extends Authenticatable implements JWTSubject
{
    use Notifiable;
    protected $table = 'utilisateurs';
    protected $fillable = ['email','mot_de_passe','role', 'is_blocked'];
    protected $hidden = ['mot_de_passe','remember_token'];

    public function setMotDePasseAttribute($value)
    {
        $this->attributes['mot_de_passe'] = Hash::make($value);
    }

    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // Role helpers
    public function isAdmin()     { return $this->role === 'admin'; }
    public function isClient()     { return $this->role === 'client'; }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role
        ];
    }
}