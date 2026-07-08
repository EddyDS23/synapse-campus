<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name','code','credits'])]
class Subject extends Model
{
    
    use HasUlids;

    public function careers(){
        return $this->belongsToMany(Career::class,'career_subjects')
                ->using(CareerSubject::class)
                ->withPivot('semester');
    }

    public function sections(){
        return $this->hasMany(Section::class);
    }

}
