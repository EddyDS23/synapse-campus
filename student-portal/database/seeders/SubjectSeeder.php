<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = [

        //['name' => '', 'code' => '', 'credits' => 5],
        // ISC — Semestre 1
        ['name' => 'Calculo Diferencial', 'code' => 'MAT101', 'credits' => 5],
        ['name' => 'Fundamentos de Programacion', 'code'=> 'ISC101', 'credits'=>5],
        ['name' => 'Algebra Lineal', 'code' => 'MAT102', 'credits' => 5],
        ['name' => 'Introduccion a las TICS', 'code' => 'ISC102', 'credits' => 5],
        
        // ISC - Semestre 2
        ['name' => 'Calculo Integral', 'code' => 'MAT201', 'credits' => 5],
        ['name' => 'Programacion Orientada a Objetos', 'code' => 'ISC201', 'credits' => 5],
        ['name' => 'Fisica', 'code' => 'FIS201', 'credits' => 5],
        ['name' => 'Estructuras Discretas', 'code' => 'MAT202', 'credits' => 5 ],

        // ISC - Semestre 3
        ['name' => 'Calculo Vectorial', 'code' => 'MAT301', 'credits' => 5],
        ['name' => 'Estructura de Datos', 'code' => 'ISC301', 'credits' => 5 ],
        ['name' => 'Sistemas Operativos I', 'code' => 'ISC302', 'credits' => 5 ],
        ['name' => 'Probabilidad y Estadistica', 'code' => 'MAT302', 'credits' => 5],

        // ISC - Semestre 4
        ['name' => 'Ecuaciones Diferenciales', 'code' => 'MAT401', 'credits' => 5],
        ['name' => 'Base de Datos', 'code' => 'ISC401', 'credits' => 5],
        ['name' => 'Redes de Computadora I', 'code' => 'ISC402', 'credits' => 5],
        ['name' => 'Arquitectura de Computadoras', 'code' => 'ISC403', 'credits' => 5],

        // ISC - Semestre 5
        ['name' => 'Ingenieria en Software', 'code' => 'ISC501', 'credits' => 5],
        ['name' => 'Sistemas Operativos II', 'code' => 'ISC502', 'credits' => 5],
        ['name' => 'Redes de computadoras II', 'code' => 'ISC503', 'credits' => 5],
        ['name' => 'Lenguaje y Automatas', 'code' => 'ISC504', 'credits' => 5],

        // ISC - Semestre 6
        ['name' => 'Desarrollo Web', 'code' => 'ISC601', 'credits' => 5],
        ['name' => 'Inteligencia Artificial', 'code' => 'ISC602', 'credits' => 5],
        ['name' => 'Administracion de Proyectos', 'code' => 'ISC603', 'credits' => 5],
        ['name' => 'Compiladores', 'code' => 'ISC604', 'credits' => 5],

        // ISC - Semestre 7
        ['name' => 'Seguridad Informatica', 'code' => 'ISC701', 'credits' => 5],
        ['name' => 'Computo en la Nube', 'code' => 'ISC702', 'credits' => 5],
        ['name' => 'Desarrollo Movil', 'code' => 'ISC703', 'credits' => 5],
        ['name' => 'Topicos Avanzados de DB', 'code' => 'ISC704', 'credits' => 5],

        // ISC - Semestre 8
        ['name' => 'Residencial Profesional', 'code' => 'ISC801', 'credits' => 10],
        ['name' => 'Emprendimiento Tecnologico', 'code' => 'ISC802', 'credits' => 5],
        ['name' => 'Etica Profesional', 'code' => 'GEN801', 'credits' => 5],
        
        // ISC - Semestre 9
        ['name' => 'Residencia Profesional II', 'code' => 'ISC901', 'credits' => 5],
        ['name' => 'Seminario de Titulacion', 'code' => 'ISC902', 'credits' => 5],

        // LAE — Semestre 1
        ['name' => 'Fundamentos de Administración', 'code' => 'LAE101', 'credits' => 5],
        ['name' => 'Contabilidad Basica', 'code' => 'LAE102', 'credits' => 5],
        ['name' => 'Matematicas Empresariales', 'code' => 'MAT103', 'credits' => 5],
        
        // LAE - Semestre 2
        ['name' => 'Recursos Humanos', 'code' => 'LAE201', 'credits' => 5],
        ['name' => 'Contabilidad Financiera', 'code' => 'LAE202', 'credits' => 5],
        ['name' => 'Estadisticas Empresariales', 'code' => 'MAT203', 'credits' => 5],

        // LAE - Semestre 3
        ['name' => 'Mercadotecnia', 'code' => 'LAE301', 'credits' => 5],
        ['name' => 'Derecho Empresarial', 'code' => 'LAE302', 'credits' => 5],
        ['name' => 'Finanzas Corporativas', 'code' => 'LAE303', 'credits' => 5],

        // LAE - Semestre 4
        ['name' => 'Administracion Estrategica', 'code' => 'LAE401', 'credits' => 5],
        ['name' => 'Gestion de Proyectos', 'code' => 'LAE402', 'credits' => 5],
        ['name' => 'Emprendimiento', 'code' => 'LAE403', 'credits' => 5],

        ];

        foreach($subjects as $subject){
            Subject::create($subject);
        }

    }
}
