<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ScheduleRepository;
use Illuminate\Support\Facades\DB;

final class ScheduleService
{
    public function __construct(
        protected ScheduleRepository $scheduleRepository,
        protected CourseService $courseService,
        protected SemesterService $semesterService
    ) {}

    public function createSchedule(array $data)
    {
        return DB::transaction(function () use ($data) {
            $courseBlock = $this->courseService->createCourseBlock([
                'course_id' => $data['course_id'],
                'semester_id' => $data['semester_id'],
                'block_code' => $data['block_code'],
            ]);

            $schedule = $this->scheduleRepository->create([
                'course_block_id' => $courseBlock->course_block_id,
                'room_id' => $data['room_id'],
                'semester_id' => $data['semester_id'],
                'block_code' => $data['block_code'],
                'schedule_type' => $data['schedule_type'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ]);

            $this->createScheduleDays($schedule->schedule_id, $data['days'] ?? []);
            $this->courseService->assignUsersToCourseBlock([
                'course_block_id' => $courseBlock->course_block_id,
                'user_ids' => $data['user_ids'] ?? [],
                'assigned_at' => now(),
            ]);

            return $schedule;
        });
    }

    public function createScheduleDays(int $scheduleId, array $days): void
    {
        foreach (array_unique($days) as $day) {
            $this->scheduleRepository->createScheduleDay([
                'schedule_id' => $scheduleId,
                'day' => $day,
                'assigned_at' => now(),
            ]);
        }
    }

    public function getUserScheduleByActiveSemester(string $userId)
    {
        $activeSemester = $this->semesterService->getActiveSemester();

        if (!$activeSemester) {
            return collect();
        }

        return $this->scheduleRepository->getUserScheduleBySemester(
            $userId,
            $activeSemester->semester_id
        );
    }
}
