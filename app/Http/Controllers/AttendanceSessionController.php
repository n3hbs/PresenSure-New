<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceSession\CreateDeviceAttendanceSessionRequest;
use App\Http\Requests\AttendanceSession\StartAttendanceSessionRequest;
use App\Services\AttendanceSessionService;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function __construct(
        protected AttendanceSessionService $attendanceSessionService
    ) {}

    public function create(CreateDeviceAttendanceSessionRequest $request)
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
}
