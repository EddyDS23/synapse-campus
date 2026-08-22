<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Override;

#[Fillable(['name', 'email', 'password','banned_at','unblocked_at','two_factor_secret','two_factor_enabled','two_factor_recovery_codes','provider','provider_id','tokens_invalidated_at','must_change_password'])]
#[Hidden(['banned_at','unblocket_at','two_factor_secret','two_factor_enabled','password', 'remember_token','two_factor_recovery_codes','provider','provider_id','tokens_invalidated_at','must_change_password'])]
class User extends Authenticatable implements JWTSubject
{

    use HasUlids;
    use HasFactory;
    use Notifiable;

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
            'banned_at' => 'datetime',
            'unblocked_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'tokens_invalidated_at'=>'datetime',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function apiSessions()
    {
        return $this->hasMany(ApiSession::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    #[Override]
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    #[Override]
    public function getJWTCustomClaims()
    {
        return [
            'email'=>$this->email,
            'aud'=>'auth-vault',
            'roles'=>$this->roles->pluck('name'),
            'scopes'=> $this->roles->flatMap(fn($role)=> $role->scopes->pluck('name'))->unique()->values(),
        ];
    }
}
