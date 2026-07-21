<?php

namespace App\Services;

use App\Models\BleDevice;
use App\Models\Schedule;
use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Validation\ValidationException;

class AttendanceSessionService
{
    public function __construct(
        protected AttendanceSessionRepository $attendanceSessionRepository,
        protected BeaconConfigurationService $beaconConfigurationService,
        protected PeriodService $periodService,
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
            $bleDevice = $this->resolveRoomDevice(
                $data['device_id'],
                $schedule
            );
            $rawToken = Str::random(64);
            $endAt = $now->copy()->addHours(2)->min($scheduleEnd);

            $period = $this->periodService->getActivePeriod();
            if ($period === null) {
                throw ValidationException::withMessages([
                    'schedule_id' => [
                        'No active attendance period exists for this semester.',
                    ],
                ]);
            }

            $session = $this->attendanceSessionRepository->create([
                'session_code' => strtoupper(Str::random(6)),
                'schedule_id' => $schedule->schedule_id,
                'period_id' => $period->period_id,
                'instructor_id' => $instructor->user_id,
                'ble_device_id' => $bleDevice->ble_device_id,
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

    private function resolveRoomDevice(
        string $deviceId,
        Schedule $schedule
    ): BleDevice {
        $bleDevice = $this->attendanceSessionRepository
            ->findBleDeviceByPublicId($deviceId);

        if ($bleDevice === null) {
            throw ValidationException::withMessages([
                'device_id' => [
                    'The selected ESP32 is not registered.',
                ],
            ]);
        }

        if (! $bleDevice->isAvailable()) {
            throw ValidationException::withMessages([
                'device_id' => [
                    'The selected ESP32 is unavailable.',
                ],
            ]);
        }

        if ((int) $bleDevice->room_id !== (int) $schedule->room_id) {
            throw ValidationException::withMessages([
                'device_id' => [
                    "{$bleDevice->device_name} belongs to room ID "
                        . "{$bleDevice->room_id}, but the schedule belongs "
                        . "to room ID {$schedule->room_id}.",
                ],
            ]);
        }

        return $bleDevice;
    }
}
