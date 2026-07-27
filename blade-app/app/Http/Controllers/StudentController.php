<?php

namespace App\Http\Controllers;

use App\Services\StudentPortalService;

use App\Exceptions\ServiceUnavailableException;

class StudentController extends Controller
{
    
    public function __construct(private StudentPortalService $studentportal){}

    public function profile():mixed{

        try {
            $data = $this->studentportal->getProfile();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message','Portal academico no disponible');
        }

        return view('student.profile',$data);

    }

    public function schedule():mixed{

        try {
            $data = $this->studentportal->getSchedule();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message','Portal academico no disponible');
        }

        return view('student.schedule',$data);
    }

    public function subjects():mixed{

        try {
            $data = $this->studentportal->getSubject();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message','Portal academico no disponible');
        }

        return view('student.subjects',$data);
    }

}
