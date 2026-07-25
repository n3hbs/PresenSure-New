<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceSession\CheckActiveAttendanceRequest;
use App\Http\Requests\AttendanceSession\CreateDeviceAttendanceSessionRequest;
use App\Http\Requests\AttendanceSession\StopAttendanceRequest;
use App\Repositories\AttendanceSessionRepository;
use App\Services\AttendanceSessionService;
use Illuminate\Http\JsonResponse;

class AttendanceSessionController extends Controller
{
    /**
     * Laravel injects the service so the controller only coordinates the HTTP
     * request and response; business rules remain in the service.
     */
    public function __construct(
        protected AttendanceSessionService $attendanceSessionService,
        protected AttendanceSessionRepository $attendanceSessionRepository
    ) {}

    /**
     * Create an attendance session for the authenticated instructor.
     */
    public function create(CreateDeviceAttendanceSessionRequest $request): JsonResponse
    {
        // validated() contains only fields allowed by the Form Request.
        // user() is supplied by Sanctum and cannot be chosen by the client.
        $result = $this->attendanceSessionService->createAttendanceSession(
            $request->validated(),
            $request->user()
        );

        // The service returns a PHP array; the controller converts it to JSON.
        return response()->json([
            'message' => 'Attendance session created successfully.',
            'data' => $result,
        ], 201);
    }

    public function stopAttendance(StopAttendanceRequest $request)
    {
        $result = $this->attendanceSessionService->endAttendanceSession((int) $request->validated('attendance_session_id'), (int) $request->validated('schedule_id'));
        return response()->json(
            $result,
            $result['success'] ? 200 : 422
        );
    }

    public function checkActive(CheckActiveAttendanceRequest $request)
    {
        $result = $this->attendanceSessionRepository->findActiveSession((int) $request->validated('schedule_id'));
        return response()->json([
            'data' => $result
        ]);
    }
}
