<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AttendanceSessionService
{
    public function __construct(
        protected AttendanceSessionRepository $attendanceSessionRepository
    ) {}

    public function createAttendanceSession(array $data, User $instructor)
    {
        return DB::transaction(function () use ($data, $instructor) {
            $schedule = $this->attendanceSessionRepository->findScheduleForSession((int) $data['schedule_id']);

            if (! $this->attendanceSessionRepository->isUserAssignedToCourseBlock($instructor->user_id, $schedule->course_block_id, )) {
                abort(403, 'You are not assign on this schedule');
            }

            if ($this->attendanceSessionRepository->hasActiveSession($schedule->schedule_id)) {
                abort(422, 'An active attendance session already exists for this schedule.');
            }

            $rawToken = Str::random(64);
            $now = now();

            $session = $this->attendanceSessionRepository->create([
                'schedule_id' => $schedule->schedule_id,
                'period_id' => $data['period_id'],
                'instructor_id' => $instructor->user_id,
                'verification_mode' => $data['verification_mode'],
                'ble_source_type' => $data['ble_source_type'],
                'beacon_id' => $data['beacon_id'] ?? null,
                'broadcaster_user_id' => $data['ble_source_type'] === 'instructor_phone'
                    ? $instructor->user_id
                    : null,
                'ble_broadcast_token' => hash('sha256', $rawToken),
                'ble_token_expires_at' => $now->copy()->addHours(2),
                'status' => 'active',
                'start_at' => $now,
                'end_at' => $now->copy()->addHours(2),
            ]);

            return [
                'session' => $session,
                'ble_token' => $rawToken,
            ];
        });
    }
}
