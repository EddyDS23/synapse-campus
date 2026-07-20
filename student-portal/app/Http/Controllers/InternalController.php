<?php

namespace App\Http\Controllers;

use App\Http\Requests\HasDebtRequest;
use App\Models\AuditLog;
use App\Models\StudentProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

use App\Services\AuditLogServiceClient;

class InternalController extends Controller
{

    public function __construct(private AuditLogServiceClient $auditLog) {}

    public function student_status(Request $request, string $studentId): JsonResponse
    {

        $student_profile = StudentProfile::with('career')->find($studentId);
        
        if ($student_profile === null) {
            return response()->json([], 404);
        }

        $statusData = [
            'student_id' => $studentId,
            'status' => $student_profile->status,
            'has_debt' => $student_profile->has_debt,
            'career' => $student_profile->career->code,
            'current_semester' => $student_profile->current_semester
        ];


        AuditLog::create([
            'action' => 'student.status.check_by_service',
            'service' => 'student_portal',
            'resource_type' => 'student_profile',
            'resource_id' => $studentId,
            'ip_address' => $request->ip(),
            'metadata' => [
                'service_id' => $request->attributes->get('jwt_payload')->sub
            ]
        ]);


        $this->auditLog->sendLog([
            'service' => 'student-portal',
            'action' => 'student.status.check_by_service',
            'resource_type' => 'student_profile',
            'resource_id' => $studentId,
            'ip_address' => $request->ip(),
            'metadata' => [
                'service_id' => $request->attributes->get('jwt_payload')->sub
            ],
        ]);

        return response()->json($statusData, 200);
    }

    public function updateDebt(HasDebtRequest $request, string $id): JsonResponse
    {

        $has_debt = $request->validated('has_debt');

        $student = StudentProfile::where('student_id', $id)->first();

        if ($student === null) {
            return response()->json([], 404);
        }

        $hasDebtStatusData = [];
        if ($has_debt === true) {
            try {
                DB::transaction(function () use ($request, &$student, &$hasDebtStatusData) {

                    $student->increment('debt_count');
                    $student->update(['has_debt' => $student->debt_count > 0]);

                    $hasDebtStatusData = [
                        'action' => 'student_profile.has_debt.updated.increment',
                        'service' => 'student-portal',
                        'resource_type' => 'student_profile',
                        'resource_id' => $student->student_id,
                        'ip_address' => $request->ip(),
                        'metadata' => [
                            'debt_count' => $student->debt_count,
                        ]
                    ];

                    AuditLog::create($hasDebtStatusData);
                });
            } catch (\Throwable $th) {
                return response()->json([], 503);
            }
            $this->auditLog->sendLog($hasDebtStatusData);
        } else {
            if ($student->debt_count <= 0) {
                return response()->json(['message'=>'Student havent debts'],409);
            }
            try {

                DB::transaction(function () use ($request, &$student, &$hasDebtStatusData) {

                    $student->decrement('debt_count');
                    $student->refresh();
                    $student->update(['has_debt' => $student->debt_count > 0]);

                    $hasDebtStatusData = [
                        'action' => 'student_profile.has_debt.updated.decrement',
                        'service' => 'student-portal',
                        'resource_type' => 'student_profile',
                        'resource_id' => $student->student_id,
                        'ip_address' => $request->ip(),
                        'metadata' => [
                            'debt_count' => $student->debt_count,
                        ]
                    ];


                    AuditLog::create($hasDebtStatusData);
                });
            } catch (\Throwable $th) {
                return response()->json([], 503);
            }

            $this->auditLog->sendLog($hasDebtStatusData);
        }

        return response()->json([], 200);
    }
}
