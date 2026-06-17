<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\User;

#[Fillable(['name'])]
class Role extends Model
{
    public $timestamps = false;

    public function users(){
        return $this->belongsToMany(User::class,'user_roles','user_id','role_id');
    }

}
