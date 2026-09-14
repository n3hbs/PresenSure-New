<?php

namespace App\Http\Resources\Student;

use App\Http\Resources\BaseResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\ScheduleResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentDetailsResource extends BaseResource
{
    public function data(Request $request): array
    {
        return [
            'user' => new UserResource($this),
            'student' => StudentResource::collection(
                $this->whenLoaded('student')
            ),
            'role' => new RoleResource(
                $this->whenLoaded('roleAssignment')?->role
            ),
            'profile' => new UserProfileResource(
                $this->whenLoaded('userProfile')
            ),
            'courses' => $this->relationLoaded('userCourseBlocks')
                ? $this->userCourseBlocks->map(function ($userCourseBlock) {
                    $courseBlock = $userCourseBlock->courseBlock;
                    $course = $courseBlock?->course;

                    return [
                        'user_course_block_id' => $userCourseBlock->user_course_block_id,
                        'course_block_id' => $courseBlock?->course_block_id,
                        'course_id' => $course?->course_id,
                        'subject_code' => $course?->subject_code,
                        'name' => $course?->name,
                        'description' => $course?->name,
                        'block_code' => $courseBlock?->block_code,
                        'schedules' => ScheduleResource::collection(
                            $courseBlock?->schedules ?? collect()
                        ),
                    ];
                })->values()
                : [],
        ];
    }
}
