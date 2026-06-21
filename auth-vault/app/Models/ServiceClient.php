<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Override;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['id','client_id','client_secret','name'])]
class ServiceClient extends Model implements JWTSubject
{

    use HasUlids;
    public $timestamps = false;


    public function scopes(){
        return $this->belongsToMany(Scope::class,'service_client_scopes','service_client_id','scope_id');
    }


    #[Override]
    public function getJWTIdentifier()
    {
        return $this->client_id;
    }

    #[Override]
    public function getJWTCustomClaims()
    {
        return [
            'scopes' => $this->scopes()->pluck('name'),
            'type' => 'service',
        ];  
    }

}
