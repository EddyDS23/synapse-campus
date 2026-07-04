<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\Section;
use App\Models\SectionSchedule;

class SectionScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $periodId = AcademicPeriod::where('is_active',true)->value('id');
        $subjects = Subject::pluck('id','code');
        $sections = Section::where('academic_period_id',$periodId)->pluck('id','subject_id');

        $schedules = [
            ['section_id'=>$sections[$subjects['MAT101']],'day_of_week'=>'monday','start_time'=>'08:00:00','end_time'=>'10:00:00'],
            ['section_id'=>$sections[$subjects['MAT101']],'day_of_week'=>'wednesday','start_time'=>'08:00:00','end_time'=>'10:00:00'],

            ['section_id'=>$sections[$subjects['ISC101']],'day_of_week'=>'monday','start_time'=>'10:00:00','end_time'=>'12:00:00'],
            ['section_id'=>$sections[$subjects['ISC101']],'day_of_week'=>'wednesday','start_time'=>'10:00:00','end_time'=>'12:00:00'],

            ['section_id'=>$sections[$subjects['MAT102']],'day_of_week'=>'tuesday','start_time'=>'08:00:00','end_time'=>'10:00:00'],
            ['section_id'=>$sections[$subjects['MAT102']],'day_of_week'=>'thursday','start_time'=>'08:00:00','end_time'=>'10:00:00'],

            ['section_id'=>$sections[$subjects['ISC101']],'day_of_week'=>'tuesday','start_time'=>'10:00:00','end_time'=>'12:00:00'],
            ['section_id'=>$sections[$subjects['ISC101']],'day_of_week'=>'thursday','start_time'=>'10:00:00','end_time'=>'12:00:00'],

        ];

        foreach($schedules as $schedule){
            SectionSchedule::create($schedule);
        }
    }
}
