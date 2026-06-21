<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

#[Fillable(['name'])]
class Role extends Model
{
    use HasUlids;

    public $timestamps = false;

    public function users(){
        return $this->belongsToMany(User::class,'user_roles','user_id','role_id');
    }

    public function scopes(){
        return $this->belongsToMany(Scope::class,'role_scopes','role_id','scope_id');
    }

}
