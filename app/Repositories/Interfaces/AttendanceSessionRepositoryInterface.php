<?php

namespace App\Repositories\Interfaces;

use App\Models\BleDevice;

interface AttendanceSessionRepositoryInterface
{
    public function create(array $data);

    public function hasActiveSession(int $schedule_id): bool;

    public function hasConflictingSession(int $scheduleId): bool;

    public function findScheduleForSession(int $schedule_id);

    public function findBleDeviceByPublicId(string $publicDeviceId): ?BleDevice;

    public function findActivePeriod(int $semesterId);

    public function findSessionForActivation(string $sessionId);

    public function isUserAssignedToCourseBlock(string $user_id, int $course_block_id): bool;
}
