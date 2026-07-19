<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Career;
use App\Models\StudentProfile;

class StudentProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careerId = Career::where('code','ISC')->value('id');
        $data = [
            'student_id'=>'01kx58x0cd2ntv3bzy65c6qfrg',
            'career_id'=>$careerId,
            'student_number'=>'2026ISC001',
            'current_semester'=>1,
            'status'=>true,
            'has_debt'=>false
        ];

        StudentProfile::create($data);

    }
}
