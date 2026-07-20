<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'access_issue'=>'Problemas de acceso',
            'academic_issue'=>'Problemas academicos',
            'library_issue'=>'Problemas de Biblioteca',
            'technical'=>'Problemas Academicos',
            'other'=>'Otros'
        ];

        foreach($categories as $name => $label){
            Category::updateOrCreate([
                'name'=>$name,
                'label'=>$label
            ]);
        }

    }
}
