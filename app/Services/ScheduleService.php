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
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ]);

            $this->createScheduleDays($schedule->schedule_id, $data['days'] ?? []);
            $this->assignUsersToCourseBlock($courseBlock->course_block_id, $data['user_ids'] ?? []);

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

    public function assignUsersToCourseBlock(int $courseBlockId, array $userIds): void
    {
        foreach (array_unique($userIds) as $userId) {
            $this->courseService->assignUserToCourseBlock([
                'user_id' => $userId,
                'course_block_id' => $courseBlockId,
                'assigned_at' => now(),
            ]);
        }
    }
}
