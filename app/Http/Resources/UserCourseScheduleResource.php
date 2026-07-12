<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCourseScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $courseBlock = $this->courseBlock;

        return [
            'user_course_block_id' => $this->user_course_block_id,
            'assigned_at' => $this->assigned_at,
            'course_block' => [
                'course_block_id' => $courseBlock->course_block_id,
                'block_code' => $courseBlock->block_code,
                'course' => [
                    'course_id' => $courseBlock->course?->course_id,
                    'subject_code' => $courseBlock->course?->subject_code,
                    'name' => $courseBlock->course?->name,
                ],
                'semester' => [
                    'semester_id' => $courseBlock->semester?->semester_id,
                    'term' => $courseBlock->semester?->term,
                    'semester_start' => $courseBlock->semester?->semester_start,
                    'semester_end' => $courseBlock->semester?->semester_end,
                ],
                'schedules' => $courseBlock->schedules->map(function ($schedule) {
                    return [
                        'schedule_id' => $schedule->schedule_id,
                        'block_code' => $schedule->block_code,
                        'start_time' => $schedule->start_time,
                        'end_time' => $schedule->end_time,
                        'days' => $schedule->scheduleDays
                            ->pluck('day')
                            ->values(),
                        'room' => [
                            'room_id' => $schedule->room?->room_id,
                            'name' => $schedule->room?->name,
                            'floor_no' => $schedule->room?->floor_no,
                            'building' => [
                                'building_id' => $schedule->room?->building?->building_id,
                                'code' => $schedule->room?->building?->code,
                                'name' => $schedule->room?->building?->name,
                            ],
                        ],
                    ];
                })->values(),
            ],
        ];
    }
}
