<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['subject_id','academic_period_id','teacher_id','group_label'])]
class Section extends Model
{
    use HasUlids;

    public function subject(){
        return $this->belongsTo(Subject::class);
    }

    public function academicPeriod(){
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function schedules(){
        return $this->hasMany(SectionSchedule::class);
    }

    public function students(){
        return $this->belongsToMany(StudentProfile::class,'enrollments','section_id','student_id');
    }

}
