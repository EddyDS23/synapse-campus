<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Career;

class CareerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $careers = [
            '1'=>[
                'name'=>'Ingenieria en Sistemas Computacionales',
                'code'=>'ISC'
            ],
            '2'=>[
                'name'=>'Licenciatura en Administracion de Empresas',
                'code'=>'LAE'
            ]
        ];

        foreach($careers as $career){
            Career::create(['name'=>$career['name'],'code'=>$career['code']]);    
        }
        
    }
}
