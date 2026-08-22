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

        return $this->successResponse(
            $result['data'],
            $result['message'],
            201
        );
    }

    public function stopAttendance(StopAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->endAttendanceSession($request->validated());

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    public function continueAttendance(ContinueAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->continueAttendanceSession($request->validated());

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    public function checkActive(CheckActiveAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionRepository->findActiveSession((int) $request->validated('schedule_id'));

        return $this->successResponse(
            $result,
            $result === null
                ? 'No active attendance session was found.'
                : 'Active attendance session retrieved successfully.'
        );
    }

    public function getSessionStudents(int $attendanceSessionId): JsonResponse
    {
        $result = $this->attendanceSessionService->getSessionStudents($attendanceSessionId);

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    public function getActiveSessionStudents(CheckActiveAttendanceRequest $request): JsonResponse
    {
        $result = $this->attendanceSessionService->getActiveSessionStudents((int) $request->validated('schedule_id'));

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }
}
