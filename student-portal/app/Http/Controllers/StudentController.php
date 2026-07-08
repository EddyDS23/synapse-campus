<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Http\Request;

use App\Models\StudentProfile;
use App\Services\AuthVaultServiceClient;
use Illuminate\Http\JsonResponse;

class StudentController extends Controller
{

    public function __construct(private AuthVaultServiceClient $authService){}
    
    public function profile(Request $request): JsonResponse{

        $studentId = $request->attributes->get('jwt_payload')->sub;

        $student_profile = StudentProfile::with('career')->find($studentId);

        if($student_profile === null){
            return response()->json(['This student havent profile'],404);
        }


        return response()->json([
            'number'=>$student_profile->student_number,
            'career'=>$student_profile->career->name,
            'semester'=>$student_profile->current_semester,
            'status'=>$student_profile->status,
            'has_debt'=>$student_profile->has_debt
        ],200);

    }

    public function schedule(Request $request): JsonResponse{

        $studentId = $request->attributes->get('jwt_payload')->sub;
        
        $student_profile = StudentProfile::with('sections.subject','sections.schedules')->find($studentId);
        
        if($student_profile === null){
            return response()->json(['This student havent profile'],404);
        }
        
        
        $scheduleData = [];

        foreach($student_profile->sections as $section){
            $subjectName = $section->subject->name;
            $group_label = $section->group_label;
            $teacher = $section->teacher_id;
            foreach($section->schedules as $schedule){
                $scheduleData[] = [
                    'subject_name'=>$subjectName,
                    'day_of_week'=>$schedule->day_of_week,
                    'start_time'=>$schedule->start_time,
                    'end_time'=>$schedule->end_time,
                    'group'=>$group_label,
                    'teacher'=>$this->authService->resolveTeacherName($teacher)
                ];
            }
        }

        return response()->json(['schedules'=>$scheduleData],200);
    }

    
    public function subjects(Request $request): JsonResponse{

        $studentId = $request->attributes->get('jwt_payload')->sub;

        $student_profile = StudentProfile::with('career.subjects')->find($studentId);

        if($student_profile === null){
            return response()->json(['This student havent profile'],404);
        }

        $flatSubjects = [];
        $semesters = [];

        foreach($student_profile->career->subjects as $subject){
            $flatSubjects[] = [
                'name'=>$subject->name,
                'code'=>$subject->code,
                'credits'=>$subject->credits,
                'semester'=>$subject->pivot->semester
            ];
        }

        foreach($flatSubjects as $subject){
            if(!array_key_exists($subject['semester'],$semesters)){
                $semesters[$subject['semester']]=[];
            }
            
            $semesters[$subject['semester']][]=$subject;
            
        }
        $groupSubjects=[];
        foreach($semesters as $semesterNumber => $subjectsInThatSemester){
            $groupSubjects[] = ['semester'=>$semesterNumber,'subjects'=>$subjectsInThatSemester];
        }

        return response()->json(['career_subjects'=>$groupSubjects],200);
    }

    public function notice(Request $request):JsonResponse{

        return response()->json(['notices'=>[]],200);

    }

    public function academic_status(Request $request):JsonResponse{

        $studentId = $request->attributes->get('jwt_payload')->sub;

        $student_profile = StudentProfile::where('student_id',$studentId)->first();

        if($student_profile === null){
            return response()->json(['This student havent profile'],404);
        }

        return response()->json([
            'status'=>$student_profile->status,
            'has_debt'=>$student_profile->has_debt
        ],200);

    }

}

