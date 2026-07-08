<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    
    public function student_status(Request $request, string $studentId):JsonResponse{

        $student_profile = StudentProfile::with('career')->find($studentId);

        if($student_profile === null){
            return response()->json([],404);
        }

        $statusData=[
            'student_id'=>$studentId,
            'status'=>$student_profile->status,
            'has_debt'=>$student_profile->has_debt,
            'career'=>$student_profile->career->code,
            'current_semester'=>$student_profile->current_semester
        ];


        AuditLog::create([
            'action'=>'student.status.check_by_service',
            'service'=>'student_portal',
            'resource_type'=>'student_profile',
            'resource_id'=>$studentId,
            'ip_address'=>$request->ip(),
            'metadata'=>[
                'service_id'=>$request->attributes->get('jwt_payload')->sub
            ]
        ]);

        return response()->json($statusData,200);
    }

}
