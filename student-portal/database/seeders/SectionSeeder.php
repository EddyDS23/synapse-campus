<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $periodId = AcademicPeriod::where('is_active',true)->value('id');
        $subjects = Subject::pluck('id','code');
        $teacherId = '01m7ykn2410qq7ymcj2c7rk3kt';
        
        $sections = [
            ['subject_id' => $subjects['MAT101'], 'academic_period_id' => $periodId, 'teacher_id' => $teacherId, 'group_label' => 'A'],
            ['subject_id' => $subjects['ISC101'], 'academic_period_id' => $periodId, 'teacher_id' => $teacherId, 'group_label' => 'A'],
            ['subject_id' => $subjects['MAT102'], 'academic_period_id' => $periodId, 'teacher_id' => $teacherId, 'group_label' => 'A'],
            ['subject_id' => $subjects['ISC102'], 'academic_period_id' => $periodId, 'teacher_id' => $teacherId, 'group_label' => 'A'],
        ];

        foreach($sections as $section){
            Section::create($section);
        }
    }
}
