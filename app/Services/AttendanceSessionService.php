<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\BleDevice;
use App\Models\Schedule;
use App\Models\User;
use App\Repositories\AttendanceSessionRepository;
use App\Repositories\BleDeviceRepository;
use App\Repositories\ScheduleRepository;
use App\Repositories\UserCourseBlockRepository;
use App\Repositories\UserRepository;
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
        protected UserRepository $userRepository,
        protected ScheduleRepository $scheduleRepository,
        protected BleDeviceRepository $bleDeviceRepository,
        protected UserCourseBlockRepository $userCourseBlockRepository
    ) {}

    public function createAttendanceSession(array $data, User $instructor): array
    {
        return DB::transaction(function () use ($data, $instructor) {
            $schedule = $this->scheduleRepository->findSchedule((int) $data['schedule_id']);

            if (! $this->userRepository->isInstructor($instructor->user_id)) {
                abort(403, 'Only instructors can create attendance sessions.');
            }
            if (! $this->userCourseBlockRepository->isUserAssignedToCourseBlock($instructor->user_id, $schedule->course_block_id)) {
                abort(403, 'You are not assigned to this schedule.');
            }
            if ($this->attendanceSessionRepository->findActiveSession($schedule->schedule_id) !== null) {
                abort(422, 'An active attendance session already exists for this schedule.');
            }
            [$now, $scheduleEnd] = $this->resolveScheduleWindow($schedule);

            $bleDevice = $this->resolveRoomDevice(
                $data['device_id'],
                $schedule
            );

            $period = $this->periodService->getActivePeriod();
            if ($period === null) {
                throw ValidationException::withMessages([
                    'schedule_id' => [
                        'No active attendance period exists for this semester.',
                    ],
                ]);
            }

            $requestedEnd = $now->copy()->addMinutes($data['requested_duration_minutes']);
            $endAt = $requestedEnd;

            if ($requestedEnd->isAfter($scheduleEnd)) {
                $endAt = $scheduleEnd;
            }

            $rawToken = Str::random(64);

            $session = $this->attendanceSessionRepository->create([
                'session_code' => strtoupper(Str::random(6)),
                'schedule_id' => $schedule->schedule_id,
                'period_id' => $period->period_id,
                'instructor_id' => $instructor->user_id,
                'ble_device_id' => $bleDevice->ble_device_id,
                'verification_mode' => $data['verification_mode'],

                'ble_broadcast_token' => hash('sha256', $rawToken),
                'ble_token_expires_at' => $endAt,

                'requires_periodic_verification' => $data['continuous_checking'],
                'status' => 'active',
                'start_at' => $now,
                'end_at' => $endAt,
            ]);

            $beaconConfiguration = $this->beaconConfigurationService->generate(
                $session,
                $bleDevice
            );

            return [
                'session' => $session->toArray(),
                'ble_token' => $rawToken,
                'beacon_configuration' => $beaconConfiguration,
            ];
        });
    }

    private function resolveScheduleWindow(Schedule $schedule): array
    {
        $now = now();

        $today = strtolower($now->englishDayOfWeek);
        $scheduledToday = false;

        foreach ($schedule->scheduleDays as $scheduleDay) {
            if ($scheduleDay->day === $today) {
                $scheduledToday = true;
                break;
            }
        }

        $scheduleStart = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->start_time);
        $scheduleEnd = $now->copy()->startOfDay()->setTimeFromTimeString($schedule->end_time);

        $isBeforeClass = $now->isBefore($scheduleStart);
        $classHasEnded = ! $now->isBefore($scheduleEnd);

        if (! $scheduledToday || $isBeforeClass || $classHasEnded) {
            throw ValidationException::withMessages([
                'schedule_id' => ['Attendance can only be started during the scheduled class window.'],
            ]);
        }

        return [$now, $scheduleEnd];
    }

    private function resolveRoomDevice(string $deviceId, Schedule $schedule): BleDevice
    {
        $bleDevice = $this->bleDeviceRepository
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


    //int $attendance_session_id, int $schedule_id, string $status
    public function editAttendanceSession(array $data, string $status)
    {
        $session = $this->attendanceSessionRepository->findAttendanceSession($data['attendance_session_id'], $data['schedule_id']);
        $schedule = $this->scheduleRepository->findSchedule((int) $data['schedule_id']);
        [, $scheduleEnd] = $this->resolveScheduleWindow($schedule);


        if ($session === null) {
            return [
                'success' => false,
                'message' => 'The attendance session was not found for this schedule.',
                'data' => null,
            ];
        }

        if ($status === 'ended') {
            $this->attendanceSessionRepository->editAttendanceStatus(
                $data['attendance_session_id'],
                [
                    'status' => 'ended',
                    'end_at' => now(),
                ]
            );

            return [
                'success' => true,
                'message' => 'Attendance session ended successfully.',
                'data' => [
                    'session' => $session->refresh()->toArray(),
                ],
            ];
        }

        if ($status === 'active') {
            $bleDevice = $this->resolveRoomDevice(
                $data['device_id'],
                $schedule
            );
            $rawToken = Str::random(64);

            $this->attendanceSessionRepository->editAttendanceStatus(
                $data['attendance_session_id'],
                [
                    'status' => 'active',
                    'end_at' => $scheduleEnd,
                    'ble_device_id' => $bleDevice->ble_device_id,
                    'ble_broadcast_token' => hash('sha256', $rawToken),
                    'ble_token_expires_at' => $scheduleEnd,
                ]
            );

            $session->refresh();

            $beaconConfiguration = $this->beaconConfigurationService->generate(
                $session,
                $bleDevice
            );

            return [
                'session' => $session->toArray(),
                'ble_token' => $rawToken,
                'beacon_configuration' => $beaconConfiguration,
            ];
        }



        return [
            'success' => false,
            'message' => 'Invalid attendance session status.',
            'data' => null,
        ];
    }

    public function continueAttendanceSession(int $attendance_session_id, int $schedule_id) {}
}
