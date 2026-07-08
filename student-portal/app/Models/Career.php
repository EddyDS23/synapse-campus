<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name','code'])]
class Career extends Model
{

    use HasUlids;
    
    public function subjects(){
        return $this->belongsToMany(Subject::class,'career_subjects')
                ->using(CareerSubject::class)
                ->withPivot('semester');
    }




}
