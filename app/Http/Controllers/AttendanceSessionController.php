<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceSession\CheckActiveAttendanceRequest;
use App\Http\Requests\AttendanceSession\ContinueAttendanceRequest;
use App\Http\Requests\AttendanceSession\CreateDeviceAttendanceSessionRequest;
use App\Http\Requests\AttendanceSession\StopAttendanceRequest;
use App\Repositories\AttendanceSessionRepository;
use App\Services\AttendanceSessionService;
use Illuminate\Http\JsonResponse;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected AttendanceSessionService $attendanceSessionService,
        protected AttendanceSessionRepository $attendanceSessionRepository
    ) {}

    public function create(CreateDeviceAttendanceSessionRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->createAttendanceSession(
            $request->validated(),
            $request->user()
        );

        return response()->json([
            'message' => 'Attendance session created successfully.',
            'data' => $result,
        ], 201);
    }

    public function stopAttendance(StopAttendanceRequest $request)
    {
        $result = $this->attendanceSessionService->editAttendanceSession($request->validated(), "ended");
        return response()->json(
            $result,
            $result['success'] ? 200 : 422
        );
    }

    public function continueAttendance(ContinueAttendanceRequest $request)
    {
        $result = $this->attendanceSessionService->editAttendanceSession($request->validated(), "active");
        return response()->json([
            'message' => 'Attendance session continued successfully.',
            'data' => $result,
        ], 200);
    }

    public function checkActive(CheckActiveAttendanceRequest $request)
    {
        $result = $this->attendanceSessionRepository->findActiveSession((int) $request->validated('schedule_id'));
        return response()->json([
            'data' => $result
        ]);
    }
}
