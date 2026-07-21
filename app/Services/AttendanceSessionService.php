<?php

namespace App\Services;

use App\Models\BleDevice;
use App\Models\Schedule;
use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function __construct(
        protected AttendanceSessionRepository $attendanceSessionRepository,
        protected BeaconConfigurationService $beaconConfigurationService
    ) {}

    public function createAttendanceSession(array $data, User $instructor): array
    {
        return DB::transaction(function () use ($data, $instructor) {
            $schedule = $this->attendanceSessionRepository->findScheduleForSession((int) $data['schedule_id']);

            if (! $this->attendanceSessionRepository->isUserAssignedToCourseBlock($instructor->user_id, $schedule->course_block_id)) {
                abort(403, 'You are not assigned to this schedule.');
            }

            if ($this->attendanceSessionRepository->hasActiveSession($schedule->schedule_id)) {
                abort(422, 'An active attendance session already exists for this schedule.');
            }

            [$now, $scheduleEnd] = $this->resolveScheduleWindow($schedule);
            $bleDevice = $this->resolveRoomBeacon($data, $schedule);
            $rawToken = Str::random(64);
            $endAt = $now->copy()->addHours(2)->min($scheduleEnd);

            $session = $this->attendanceSessionRepository->create([
                'session_code' => strtoupper(Str::random(6)),
                'schedule_id' => $schedule->schedule_id,
                'period_id' => $data['period_id'],
                'instructor_id' => $instructor->user_id,
                'verification_mode' => $data['verification_mode'],
                'ble_broadcast_token' => hash('sha256', $rawToken),
                'ble_token_expires_at' => $endAt,
                'requires_periodic_verification' => $data['requires_periodic_verification'] ?? false,
                'status' => 'active',
                'start_at' => $now,
                'end_at' => $endAt,
            ]);

            $beaconConfiguration = $bleDevice === null
                ? null
                : $this->beaconConfigurationService->generate($session, $bleDevice);

            return [
                'session' => array_replace($session->toArray(), [
                    'ble_broadcast_token' => $rawToken,
                ]),
                'ble_token' => $rawToken,
                'beacon_configuration' => $beaconConfiguration,
            ];
        });
    }

    private function resolveScheduleWindow(Schedule $schedule): array
    {
        $now = now();
        $scheduledToday = $schedule->scheduleDays->contains(
            'day',
            strtolower($now->englishDayOfWeek)
        );
        $scheduleStart = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->start_time);
        $scheduleEnd = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->end_time);

        if (! $scheduledToday || $now->lt($scheduleStart) || $now->gte($scheduleEnd)) {
            throw ValidationException::withMessages([
                'schedule_id' => ['Attendance can only be started during the scheduled class window.'],
            ]);
        }

        return [$now, $scheduleEnd];
    }

    private function resolveRoomBeacon(array $data, Schedule $schedule): ?BleDevice
    {
        if ($data['ble_source_type'] !== 'room_beacon') {
            return null;
        }

        $bleDevice = $this->attendanceSessionRepository->findBleDeviceByPublicId($data['beacon_id']);

        if ($bleDevice === null) {
            throw ValidationException::withMessages([
                'beacon_id' => ['The selected ESP32 is not registered.'],
            ]);
        }

        if (! $bleDevice->is_active) {
            throw ValidationException::withMessages([
                'beacon_id' => ['The selected ESP32 is inactive.'],
            ]);
        }

        if ($bleDevice->room_id !== $schedule->room_id) {
            throw ValidationException::withMessages([
                'beacon_id' => ['The selected ESP32 is not assigned to the scheduled room.'],
            ]);
        }

        return $bleDevice;
    }
}
