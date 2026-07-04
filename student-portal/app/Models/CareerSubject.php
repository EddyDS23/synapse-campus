<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


#[Fillable(['career_id','subject_id','semester'])]
class CareerSubject extends Pivot
{
    
    public $incrementing = false;
    protected $table = 'career_subjects';

    public function career(){
        return $this->belongsTo(Career::class);
    }

    public function subject(){
        return $this->belongsTo(Subject::class);
    }
}

