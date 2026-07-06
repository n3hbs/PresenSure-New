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

            return $this->scheduleRepository->create([
                'course_block_id' => $courseBlock->course_block_id,
                'room_id' => $data['room_id'],
                'semester_id' => $data['semester_id'],
                'block_code' => $data['block_code'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
            ]);
        });
    }
}
