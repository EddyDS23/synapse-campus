<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\AcademicPeriod;

class AcademicPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AcademicPeriod::create([
            'year'=>2026,
            'number'=>2,
            'start_date'=>now(),
            'end_date'=>now()->addMonths(6),
            'is_active'=>true //Un periodo los demas desactivados nivel codigo bd no valida
        ]);
    }
}
