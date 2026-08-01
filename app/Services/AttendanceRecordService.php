<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AttendanceRecordRepository;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\BleDetectionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceRecordService
{
    public function __construct(
        protected AttendanceRecordRepository $attendanceRecordRepository,
        protected AttendanceSessionRepository $attendanceSessionRepository,
        protected BleDetectionRepository $bleDetectionRepository
    ) {}

    public function createAttendanceRecord(array $data, User $student)
    {
        return DB::transaction(function () use ($data, $student) {
            $attendanceSession = $this->attendanceSessionRepository->findActiveSession($data['schedule_id']);
            if ($attendanceSession === null) {
                throw ValidationException::withMessages([
                    'schedule_id' => [
                        'No active attendance session exists for this schedule.',
                    ],
                ]);
            }

            $attendanceRecord = $this->attendanceRecordRepository->create([
                'attendance_session_id' => $attendanceSession->attendance_session_id,
                'student_id' => $student->user_id,
                'presence_verified' => $data['presence_verified'],
                'face_verified' => $data['face_verified'],
                'face_verified_at' => $data['face_verified_at'],
                'verified_at' => $data['verified_at'],
                'status' => 'present',
            ]);

            $bleDetection = $this->bleDetectionRepository->create([
                'attendance_record_id' => $attendanceRecord->attendance_record_id,
                'user_id' => $student->user_id,
                'rssi' => $data['rssi'],
                'detected_at' => $data['detected_at'],
            ]);

            return [
                'success' => true,
                'message' => 'Attendance record created successfully.',
                'data' => [
                    'attendanceRecord' => $attendanceRecord,
                    'bleDetection' => $bleDetection,
                ],
            ];
        });
    }
}
