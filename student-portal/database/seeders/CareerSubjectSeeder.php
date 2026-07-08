<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Subject;
use App\Models\Career;
use App\Models\CareerSubject;

class CareerSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Obtener Carreras
        $isc = Career::where('code','ISC')->value('id');
        $lae = Career::where('code','LAE')->value('id');

        // Obtener todas las materias y meter lo en un array ['code'=>'id']
        $subjects = Subject::pluck('id','code');

        $relations = [

            // ISC - Semestre 1
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT101'], 'semester'=> 1],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC101'], 'semester'=> 1],
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT102'], 'semester'=> 1],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC102'], 'semester'=> 1],

            // ISC - Semestre 2
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT201'], 'semester'=> 2],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC201'], 'semester'=> 2],
            ['career_id'=> $isc, 'subject_id'=> $subjects['FIS201'], 'semester'=> 2],
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT202'], 'semester'=> 2],

            // ISC - Semestre 3
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT301'], 'semester'=> 3],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC301'], 'semester'=> 3],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC302'], 'semester'=> 3],
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT302'], 'semester'=> 3],

            // ISC - Semestre 4
            ['career_id'=> $isc, 'subject_id'=> $subjects['MAT401'], 'semester'=> 4],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC401'], 'semester'=> 4],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC402'], 'semester'=> 4],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC403'], 'semester'=> 4],

            // ISC - Semestre 5
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC501'], 'semester'=> 5],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC502'], 'semester'=> 5],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC503'], 'semester'=> 5],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC504'], 'semester'=> 5],

            // ISC - Semestre 6
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC601'], 'semester'=> 6],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC602'], 'semester'=> 6],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC603'], 'semester'=> 6],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC604'], 'semester'=> 6],

            // ISC - Semestre 7
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC701'], 'semester'=> 7],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC702'], 'semester'=> 7],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC703'], 'semester'=> 7],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC704'], 'semester'=> 7],

            // ISC - Semestre 8
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC801'], 'semester'=> 8],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC802'], 'semester'=> 8],
            ['career_id'=> $isc, 'subject_id'=> $subjects['GEN801'], 'semester'=> 8],

            // ISC - Semestre 9
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC901'], 'semester'=> 9],
            ['career_id'=> $isc, 'subject_id'=> $subjects['ISC902'], 'semester'=> 9],
        
            // LAE - Semestre 1
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE101'], 'semester'=> 1],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE102'], 'semester'=> 1],
            ['career_id'=> $lae, 'subject_id'=> $subjects['MAT103'], 'semester'=> 1],
            

            // LAE - Semestre 2
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE201'], 'semester'=> 2],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE202'], 'semester'=> 2],
            ['career_id'=> $lae, 'subject_id'=> $subjects['MAT203'], 'semester'=> 2],
            

            // LAE - Semestre 3
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE301'], 'semester'=> 3],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE302'], 'semester'=> 3],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE303'], 'semester'=> 3],
            

            // LAE - Semestre 4
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE401'], 'semester'=> 4],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE402'], 'semester'=> 4],
            ['career_id'=> $lae, 'subject_id'=> $subjects['LAE403'], 'semester'=> 4],
            
    
            ];
        

        foreach($relations as $relation){
            CareerSubject::create($relation);
        }
    }
}
