<?php

namespace Database\Seeders;

use App\Models\CareerSubject;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Database\Seeders\AcademicPeriodSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {   
        $this->call(CareerSeeder::class);
        $this->call(SubjectSeeder::class);
        $this->call(CareerSubjectSeeder::class); 
        $this->call(AcademicPeriodSeeder::class);
        $this->call(SectionSeeder::class);
        $this->call(SectionScheduleSeeder::class);
        $this->call(StudentProfileSeeder::class);
        $this->call(EnrollmentSeeder::class);
    }
}
