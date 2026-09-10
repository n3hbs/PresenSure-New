<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceSession\CheckActiveAttendanceRequest;
use App\Http\Requests\AttendanceSession\ContinueAttendanceRequest;
use App\Http\Requests\AttendanceSession\CreateDeviceAttendanceSessionRequest;
use App\Http\Requests\AttendanceSession\StopAttendanceRequest;
use App\Http\Resources\AttendanceSession\AttendanceSessionStudentsResource;
use App\Http\Resources\AttendanceSession\CreateAttendanceSessionResource;
use App\Http\Resources\AttendanceSessionResource;
use App\Services\AttendanceSessionService;
use Illuminate\Http\JsonResponse;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected AttendanceSessionService $attendanceSessionService
    ) {}

    public function create(CreateDeviceAttendanceSessionRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->createAttendanceSession(
            $request->validated(),
            $request->user()
        );

        return $this->successResponse(
            new CreateAttendanceSessionResource($result),
            'Attendance session created successfully.',
            201
        );
    }

    public function stopAttendance(StopAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->endAttendanceSession($request->validated());

        return $this->successResponse(
            new AttendanceSessionResource($result),
            'Attendance session ended successfully.'
        );
    }

    public function continueAttendance(ContinueAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->continueAttendanceSession($request->validated());

        return $this->successResponse(
            new CreateAttendanceSessionResource($result),
            'Attendance session continued successfully.'
        );
    }

    public function checkActive(CheckActiveAttendanceRequest $request): JsonResponse
    {
        $session = $this->attendanceSessionService->findActiveSession((int) $request->validated('schedule_id'));

        return $this->successResponse(
            new AttendanceSessionResource($session),
            'Active attendance session retrieved successfully.'
        );
    }

    public function getSessionStudents(int $attendanceSessionId): JsonResponse
    {
        $result = $this->attendanceSessionService->getSessionStudents($attendanceSessionId);

        return $this->successResponse(
            new AttendanceSessionStudentsResource($result),
            'Attendance session student list retrieved successfully.'
        );
    }

    public function getActiveSessionStudents(CheckActiveAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->getActiveSessionStudents((int) $request->validated('schedule_id'));

        return $this->successResponse(
            new AttendanceSessionStudentsResource($result),
            'Active attendance session student list retrieved successfully.'
        );
    }
}
