<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

#[Fillable(['name'])]
class Scope extends Model
{
    
    use HasUlids;

    public $timestamps = false;

    public function roles(){
        return $this->hasMany(Role::class);
    }

}
