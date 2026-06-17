<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password','banned_at','unblocked_at','two_factor_secret','two_factor_enabled','two_factor_recovery_codes','provider','provider_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens,HasFactory, Notifiable;

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
            'banned_at'=>'datetime',
            'unblocked_at'=>'datetime',
            'two_factor_enabled'=>'boolean',
            'two_factor_recovery_codes'=>'array'
        ];
    }

    public function roles(){
        return $this->belongsToMany(Role::class,'user_roles','user_id','role_id');
    }

    public function apiSessions(){
        return $this->hasMany(ApiSession::class);
    }

    public function auditLogs(){
        return $this->hasMany(AuditLog::class);
    }
}
