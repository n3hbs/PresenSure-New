<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AttendanceRecordRepository;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\BleDetectionRepository;
use Illuminate\Validation\ValidationException;

class BleDetectionService
{
    public function __construct(
        protected BleDetectionRepository $bleDetectionRepository,
        protected AttendanceRecordRepository $attendanceRecordRepository,
        protected AttendanceSessionRepository $attendanceSessionRepository
    ) {}

    public function createBleDetection(array $data, User $student)
    {
        $attendanceSession = $this->attendanceSessionRepository->findActiveSession($data['schedule_id']);
        if ($attendanceSession === null) {
            throw ValidationException::withMessages([
                'schedule_id' => [
                    'No active attendance session exists for this schedule.',
                ],
            ]);
        }
        if (!$attendanceSession->requires_periodic_verification) {
            throw ValidationException::withMessages([
                'schedule_id' => [
                    'BLE detection is not enabled for this schedule.',
                ],
            ]);
        }

        $attendanceRecord = $this->attendanceRecordRepository->getAttendanceRecord($data['schedule_id'], $student->user_id);
        if ($attendanceRecord === null) {
            throw ValidationException::withMessages([
                'schedule_id' => [
                    'No attendance record exists for this schedule.',
                ],
            ]);
        }

        $bleDetection = $this->bleDetectionRepository->create([
            'attendance_record_id' => $attendanceRecord->attendance_record_id,
            'user_id' => $student->user_id,
            'rssi' => $data['rssi'],
            'detected_at' => $data['detected_at'],
        ]);

        return [
            'success' => true,
            'message' => 'BLE detection recorded successfully.',
            'data' => [
                'bleDetection' => $bleDetection,
            ],
        ];
    }
}
