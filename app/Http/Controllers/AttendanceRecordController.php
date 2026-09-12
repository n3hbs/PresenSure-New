<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRecord\CheckAttendanceRecordRequest;
use App\Http\Requests\AttendanceRecord\StoreAttendanceRecordRequest;
use App\Http\Resources\AttendanceRecordResource;
use App\Http\Resources\CreateAttendanceRecordResource;
use App\Services\AttendanceRecordService;
use Illuminate\Http\JsonResponse;

class AttendanceRecordController extends Controller
{
    public function __construct(
        protected AttendanceRecordService $attendanceRecordService
    ) {}

    public function create(StoreAttendanceRecordRequest $request): JsonResponse
    {
        $result = $this->attendanceRecordService->createAttendanceRecord(
            $request->validated(),
            $request->user()
        );

        return $this->successResponse(
            new CreateAttendanceRecordResource($result),
            'Attendance record created successfully.',
            201
        );
    }

    public function checkRecord(CheckAttendanceRecordRequest $request): JsonResponse
    {
        $scheduleId = (int) ($request->validated('schedule_id') ?? $request->validated('attendance_schedule_id'));
        $record = $this->attendanceRecordService->getAttendanceRecord($scheduleId, $request->user()->user_id);

        return $this->successResponse(
            new AttendanceRecordResource($record),
            'Active attendance record retrieved successfully.'
        );
    }
}
