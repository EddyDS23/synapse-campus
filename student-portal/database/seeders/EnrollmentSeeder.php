<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Section;
use App\Models\Subject;
use App\Models\AcademicPeriod;
use App\Models\StudentProfile;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodId = AcademicPeriod::where('is_active',true)->value('id');
        $subjects = Subject::pluck('id','code');
        $sections = Section::where('academic_period_id',$periodId)->pluck('id','subject_id');
        $user = StudentProfile::where('student_id','01kx58x0cd2ntv3bzy65c6qfrg')->first();


        $enrollments = [
            ['section_id'=>$sections[$subjects['MAT101']]],
            ['section_id'=>$sections[$subjects['ISC101']]],
            ['section_id'=>$sections[$subjects['ISC102']]],
            ['section_id'=>$sections[$subjects['MAT102']]],
        ];

        foreach($enrollments as $enrollment){
            $user->sections()->attach($enrollment['section_id']);
        }

    }
}
