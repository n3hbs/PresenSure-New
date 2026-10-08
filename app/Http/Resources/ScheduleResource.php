<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'schedule_id' => $this->schedule_id,
            'course_block_id' => $this->course_block_id,
            'block_code' => $this->block_code,
            'schedule_type' => $this->schedule_type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'days' => $this->relationLoaded('scheduleDays')
                ? $this->scheduleDays->pluck('day')->values()
                : [],
            'course' => $this->when(
                $this->relationLoaded('courseBlock') && $this->courseBlock && $this->courseBlock->relationLoaded('course') && $this->courseBlock->course,
                function () {
                    return [
                        'course_id' => $this->courseBlock->course->course_id,
                        'subject_code' => $this->courseBlock->course->subject_code,
                        'course_code' => $this->courseBlock->course->subject_code,
                        'name' => $this->courseBlock->course->name,
                    ];
                }
            ),
            'course_block' => $this->when(
                $this->relationLoaded('courseBlock') && $this->courseBlock,
                function () {
                    return [
                        'course_block_id' => $this->courseBlock->course_block_id,
                        'block_code' => $this->courseBlock->block_code,
                    ];
                }
            ),
            'instructor' => $this->when(
                $this->relationLoaded('courseBlock') && $this->courseBlock,
                function () {
                    if ($this->courseBlock->relationLoaded('instructor') && $this->courseBlock->instructor) {
                        return [
                            'user_id' => $this->courseBlock->instructor->user_id,
                            'name' => $this->courseBlock->instructor->name,
                            'image' => $this->courseBlock->instructor->image,
                        ];
                    }
                    if ($this->courseBlock->relationLoaded('userCourseBlocks') && $this->courseBlock->userCourseBlocks) {
                        $instructorUcb = $this->courseBlock->userCourseBlocks->first(function ($ucb) {
                            if ($ucb->relationLoaded('user') && $ucb->user) {
                                if ($ucb->user->instructor !== null || strcasecmp((string) $ucb->user->role_name, 'instructor') === 0) {
                                    return true;
                                }
                                if (strcasecmp((string) $ucb->user->role_name, 'student') === 0) {
                                    return false;
                                }
                            }

                            $uid = (string) ($ucb->user?->user_id ?? $ucb->user_id);

                            if (str_starts_with(strtoupper($uid), 'C-') || str_starts_with(strtoupper($uid), 'STUDENT')) {
                                return false;
                            }

                            return true;
                        });
                        if ($instructorUcb && $instructorUcb->user) {
                            return [
                                'user_id' => $instructorUcb->user->user_id,
                                'name' => trim(($instructorUcb->user->first_name ?? '').' '.($instructorUcb->user->last_name ?? '')),
                                'image' => $instructorUcb->user->image,
                            ];
                        }
                    }

                    return null;
                }
            ),
            'semester' => $this->when(
                $this->relationLoaded('semester') && $this->semester,
                function () {
                    return [
                        'semester_id' => $this->semester->semester_id,
                        'semester_name' => $this->semester->semester_name,
                        'academic_year' => $this->semester->schoolYear?->academic_year,
                    ];
                }
            ),
            'room' => new RoomResource(
                $this->whenLoaded('room')
            ),
        ];
    }
}
