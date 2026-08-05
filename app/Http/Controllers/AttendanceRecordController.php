<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceRecord\CheckAttendanceRecordRequest;
use App\Http\Requests\AttendanceRecord\StoreAttendanceRecordRequest;
use App\Models\AttendanceRecord;
use App\Repositories\AttendanceRecordRepository;
use App\Services\AttendanceRecordService;

class AttendanceRecordController extends Controller
{

    public function __construct(
        protected AttendanceRecordService $attendanceRecordService,
        protected AttendanceRecordRepository $attendanceRecordRepository,
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(StoreAttendanceRecordRequest $request)
    {
        $result = $this->attendanceRecordService->createAttendanceRecord($request->validated(), $request->user());

        return $this->successResponse(
            $result['data'],
            $result['message'],
            201
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AttendanceRecord $attendanceRecord)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AttendanceRecord $attendanceRecord)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AttendanceRecord $attendanceRecord)
    {
        //
    }

    public function checkRecord(CheckAttendanceRecordRequest $request)
    {
        $scheduleId = (int) ($request->validated('schedule_id') ?? $request->validated('attendance_schedule_id'));
        $result = $this->attendanceRecordRepository->getAttendanceRecord($scheduleId, $request->user()->user_id);

        return $this->successResponse(
            $result,
            $result === null
                ? 'No active attendance record was found.'
                : 'Active attendance record retrieved successfully.',
            200
        );
    }

}
