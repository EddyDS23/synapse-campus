<?php

namespace App\Models;

use Carbon\Doctrine\CarbonTypeConverter;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Override;

#[Fillable(['student_id','career_id','student_number','current_semester','status','has_debt','debt_count'])]
#[Hidden(['created_at','debt_count'])]
class StudentProfile extends Model
{

    protected $primaryKey = 'student_id';
    public $incrementing = false;
    public $keyType = 'string';


    #[Override]
    public function casts()
    {
        return [
            'has_debt'=>'boolean'
        ];
    }

    public function career(){
        return $this->belongsTo(Career::class);
    }

    public function sections(){
        return $this->belongsToMany(Section::class,'enrollments','student_id','section_id');
    }



}
