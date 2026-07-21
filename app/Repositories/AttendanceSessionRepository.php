<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use App\Models\Period;
use App\Models\Schedule;
use App\Models\UserCourseBlock;
use App\Repositories\Interfaces\AttendanceSessionRepositoryInterface;

class AttendanceSessionRepository implements AttendanceSessionRepositoryInterface
{
    public function create(array $data)
    {
        return AttendanceSession::create($data);
    }

    public function hasActiveSession(int $schedule_id): bool
    {
        return AttendanceSession::where('schedule_id', $schedule_id)
            ->where('status', 'active')
            ->exists();
    }

    public function hasConflictingSession(int $scheduleId): bool
    {
        return AttendanceSession::where('schedule_id', $scheduleId)
            ->whereIn('status', ['pending_device_confirmation', 'active'])
            ->where('end_at', '>', now())
            ->exists();
    }

    public function findScheduleForSession(int $schedule_id)
    {
        return Schedule::with('scheduleDays')
            ->findOrFail($schedule_id);
    }

    public function findBleDeviceByPublicId(string $publicDeviceId): ?BleDevice
    {
        return BleDevice::where('public_device_id', $publicDeviceId)->first();
    }

    public function findActivePeriod(int $semesterId): ?Period
    {
        return Period::where('semester_id', $semesterId)
            ->whereDate('period_start', '<=', today())
            ->whereDate('period_end', '>=', today())
            ->first();
    }

    public function findSessionForActivation(string $sessionId): ?AttendanceSession
    {
        return AttendanceSession::where('session_uuid', $sessionId)
            ->lockForUpdate()
            ->first();
    }

    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool
    {
        return UserCourseBlock::where('user_id', $user_id)
            ->where('course_block_id', $course_block_id)
            ->exists();
    }
}
