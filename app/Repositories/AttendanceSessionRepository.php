<?php

namespace App\Repositories;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use App\Models\Schedule;
use App\Models\UserCourseBlock;
use App\Models\UserRole;
use App\Repositories\Interfaces\AttendanceSessionRepositoryInterface;
use Override;

class AttendanceSessionRepository implements AttendanceSessionRepositoryInterface
{
    /**
     * Confirm that the authenticated account has the instructor role.
     */
    public function isInstructor(string $userId): bool
    {
        return UserRole::where('user_id', $userId)
            ->whereHas('role', function ($query) {
                $query->where('role_name', 'instructor');
            })
            ->exists();
    }

    /**
     * Insert a validated attendance-session record and return its model.
     */
    public function create(array $data)
    {
        return AttendanceSession::create($data);
    }

    /**
     * Determine whether this schedule already has an active session.
     */
    public function findActiveSession(int $schedule_id)
    {
        $now = now();
        return AttendanceSession::where('schedule_id', $schedule_id)
            ->where('status', 'active')
            ->where('start_at', '<=' , $now)
            ->where('end_at', '>', $now)
            ->first();
    }

    /**
     * Load the selected schedule and eagerly load its meeting days so the
     * service can validate whether the class is scheduled today.
     */
    public function findScheduleForSession(int $schedule_id)
    {
        return Schedule::with('scheduleDays')
            ->findOrFail($schedule_id);
    }

    /**
     * Translate the client-facing ESP32 ID into the internal BleDevice model.
     */
    public function findBleDeviceByPublicId(string $publicDeviceId): ?BleDevice
    {
        return BleDevice::where('public_device_id', $publicDeviceId)->first();
    }

    /**
     * Check that the authenticated user is assigned to the schedule's course
     * block. The service uses the result as an authorization business rule.
     */
    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool
    {
        return UserCourseBlock::where('user_id', $user_id)
            ->where('course_block_id', $course_block_id)
            ->exists();
    }

    public function endAttendanceSession(int $attendanceSession_id, array $data)
    {
        return AttendanceSession::where('attendance_session_id', $attendanceSession_id)
        ->update($data);
    }
}
