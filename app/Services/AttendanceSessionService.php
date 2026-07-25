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
    /**
     * Inject the collaborators responsible for attendance persistence, beacon
     * configuration, and resolving the active academic period.
     */
    public function __construct(
        protected AttendanceSessionRepository $attendanceSessionRepository,
        protected BeaconConfigurationService $beaconConfigurationService,
        protected PeriodService $periodService,
    ) {}

    public function createAttendanceSession(array $data, User $instructor): array
    {
        // A failure anywhere in this callback rolls back the session insert.
        return DB::transaction(function () use ($data, $instructor) {
            // Load the schedule together with its scheduled meeting days.
            $schedule = $this->attendanceSessionRepository->findScheduleForSession((int) $data['schedule_id']);

            // The authenticated account must have the instructor role.
            if (! $this->attendanceSessionRepository->isInstructor($instructor->user_id)) {
                abort(403, 'Only instructors can create attendance sessions.');
            }

            // Only an instructor assigned to this course block may start it.
            if (! $this->attendanceSessionRepository->isUserAssignedToCourseBlock($instructor->user_id, $schedule->course_block_id)) {
                abort(403, 'You are not assigned to this schedule.');
            }

            // Prevent two active attendance sessions for the same schedule.
            if ($this->attendanceSessionRepository->findActiveSession($schedule->schedule_id) !== null) {
                abort(422, 'An active attendance session already exists for this schedule.');
            }

            // Confirm the class occurs now and get the latest allowed end time.
            [$now, $scheduleEnd] = $this->resolveScheduleWindow($schedule);

            // Look up the ESP32 by its public ID and validate its room/status.
            $bleDevice = $this->resolveRoomDevice(
                $data['device_id'],
                $schedule
            );

            // Attach the session to the currently active academic period.
            $period = $this->periodService->getActivePeriod();
            if ($period === null) {
                throw ValidationException::withMessages([
                    'schedule_id' => [
                        'No active attendance period exists for this semester.',
                    ],
                ]);
            }

            // Calculate the requested end time, but never pass the class end.
            $requestedEnd = $now->copy()->addMinutes($data['requested_duration_minutes']);
            $endAt = $requestedEnd;

            if ($requestedEnd->isAfter($scheduleEnd)) {
                $endAt = $scheduleEnd;
            }

            // The raw token is returned once; only its hash is stored.
            $rawToken = Str::random(64);

            // Persist the validated session data through the repository.
            $session = $this->attendanceSessionRepository->create([
                // A short public code identifies the attendance session.
                'session_code' => strtoupper(Str::random(6)),
                'schedule_id' => $schedule->schedule_id,
                'period_id' => $period->period_id,
                'instructor_id' => $instructor->user_id,
                'ble_device_id' => $bleDevice->ble_device_id,
                'verification_mode' => $data['verification_mode'],

                // Hashing avoids saving the usable raw BLE token in the table.
                'ble_broadcast_token' => hash('sha256', $rawToken),
                'ble_token_expires_at' => $endAt,

                'requires_periodic_verification' => $data['continuous_checking'],
                'status' => 'active',
                'start_at' => $now,
                'end_at' => $endAt,
            ]);

            // Build the signed settings that will be passed to the ESP32.
            $beaconConfiguration = $this->beaconConfigurationService->generate(
                $session,
                $bleDevice
            );

            // Return service data. The controller later serializes it as JSON.
            return [
                'session' => $session->toArray(),
                'ble_token' => $rawToken,
                'beacon_configuration' => $beaconConfiguration,
            ];
        });
    }

    private function resolveScheduleWindow(Schedule $schedule): array
    {
        // Capture one current timestamp so every comparison uses the same time.
        $now = now();

        // Match today's lowercase English day name against schedule_days.day.
        $today = strtolower($now->englishDayOfWeek);
        $scheduledToday = false;

        foreach ($schedule->scheduleDays as $scheduleDay) {
            if ($scheduleDay->day === $today) {
                $scheduledToday = true;
                break;
            }
        }

        // Combine today's date with the schedule's stored start and end times.
        $scheduleStart = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->start_time);
        $scheduleEnd = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->end_time);

        $isBeforeClass = $now->isBefore($scheduleStart);
        $classHasEnded = ! $now->isBefore($scheduleEnd);

        // Starting is allowed only on the correct day and inside [start, end).
        if (! $scheduledToday || $isBeforeClass || $classHasEnded) {
            throw ValidationException::withMessages([
                'schedule_id' => ['Attendance can only be started during the scheduled class window.'],
            ]);
        }

        // The caller needs both the session start and maximum ending time.
        return [$now, $scheduleEnd];
    }

    /**
     * Find the requested ESP32 and ensure it can serve this schedule.
     */
    private function resolveRoomDevice(string $deviceId, Schedule $schedule): BleDevice
    {
        // deviceId is the public string printed/configured on the ESP32.
        $bleDevice = $this->attendanceSessionRepository
            ->findBleDeviceByPublicId($deviceId);

        // Reject an ID that is not registered in ble_devices.
        if ($bleDevice === null) {
            throw ValidationException::withMessages([
                'device_id' => [
                    'The selected ESP32 is not registered.',
                ],
            ]);
        }

        // BleDevice decides availability from its current status.
        if (! $bleDevice->isAvailable()) {
            throw ValidationException::withMessages([
                'device_id' => [
                    'The selected ESP32 is unavailable.',
                ],
            ]);
        }

        // A room beacon cannot be used for a class scheduled in another room.
        if ((int) $bleDevice->room_id !== (int) $schedule->room_id) {
            throw ValidationException::withMessages([
                'device_id' => [
                    "{$bleDevice->device_name} belongs to room ID "
                        . "{$bleDevice->room_id}, but the schedule belongs "
                        . "to room ID {$schedule->room_id}.",
                ],
            ]);
        }

        // The return type guarantees that callers receive a valid BleDevice.
        return $bleDevice;
    }

    public function endAttendanceSession(int $attendance_session_id, int $schedule_id)
    {
        $activeSession = $this->attendanceSessionRepository
            ->findActiveSession($schedule_id);

        if ($activeSession === null) {
            return [
                'success' => false,
                'message' => 'No active attendance session was found.',
                'data' => null,
            ];
        }

        if ((int) $activeSession->attendance_session_id !== $attendance_session_id) {
            return [
                'success' => false,
                'message' => 'The attendance session does not match the active session.',
                'data' => null,
            ];
        }

        $this->attendanceSessionRepository->endAttendanceSession($attendance_session_id, [
            'status' => 'ended',
            'end_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Attendance session ended successfully.',
            'data' => $activeSession->refresh(),
        ];
    }
}
