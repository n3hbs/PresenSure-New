<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // 1. Resolve instructor from direct relation or from userCourseBlocks
        $instructor = $this->relationLoaded('instructor') ? $this->instructor : null;
        if (! $instructor && $this->relationLoaded('userCourseBlocks') && $this->userCourseBlocks) {
            $instructorUcb = $this->userCourseBlocks->first(function ($ucb) {
                // If relation is loaded, prioritize checking instructor role or model
                if ($ucb->relationLoaded('user') && $ucb->user) {
                    if ($ucb->user->instructor !== null || strcasecmp((string) $ucb->user->role_name, 'instructor') === 0) {
                        return true;
                    }
                    if (strcasecmp((string) $ucb->user->role_name, 'student') === 0) {
                        return false;
                    }
                }

                $rawId = (string) $ucb->user_id;

                // Student IDs start with 'C-' (e.g. C-2022-0138, C-0000-0000)
                // Instructor IDs have NO 'C-' prefix (e.g. 2022-0138, 2000-0000)
                if (str_starts_with(strtoupper($rawId), 'C-') || str_starts_with(strtoupper($rawId), 'STUDENT')) {
                    return false;
                }

                return true;
            });
            $instructor = $instructorUcb?->user;
        }

        // 2. Count real students (students have C- prefix or non-instructor accounts)
        $studentsCount = $this->relationLoaded('userCourseBlocks')
            ? $this->userCourseBlocks->filter(function ($u) use ($instructor) {
                // Omit the resolved instructor
                if ($instructor && (string) $u->user_id === (string) $instructor->user_id) {
                    return false;
                }

                // If user relation indicates instructor role/model, omit
                if ($u->relationLoaded('user') && $u->user) {
                    if ($u->user->instructor !== null || strcasecmp((string) $u->user->role_name, 'instructor') === 0) {
                        return false;
                    }
                }

                $rawId = (string) $u->user_id;

                // Student IDs start with 'C-'
                if (str_starts_with(strtoupper($rawId), 'C-')) {
                    return true;
                }

                // Non-instructor accounts
                return strcasecmp((string) ($u->user?->role_name ?? ''), 'instructor') !== 0;
            })->count()
            : (int) ($this->students_count ?? 0);

        return [
            'course_block_id' => $this->course_block_id,
            'course_id' => $this->course_id,
            'semester_id' => $this->semester_id,
            'block_code' => $this->block_code,
            'instructor_id' => $this->instructor_id ?? ($instructor?->user_id ?? null),
            'instructor' => $instructor ? [
                'user_id' => $instructor->user_id,
                'first_name' => $instructor->first_name,
                'last_name' => $instructor->last_name,
                'name' => trim("{$instructor->first_name} {$instructor->last_name}"),
                'full_name' => trim("{$instructor->first_name} {$instructor->last_name}"),
            ] : null,
            'students_count' => $studentsCount,
            'schedules_count' => (int) ($this->schedules_count ?? ($this->relationLoaded('schedules') ? $this->schedules->count() : 0)),
            'course' => $this->whenLoaded('course', function () {
                return new CourseResource($this->course);
            }),
            'semester' => $this->whenLoaded('semester', function () {
                return new SemesterResource($this->semester);
            }),
            'schedules' => $this->whenLoaded('schedules', function () {
                return ScheduleResource::collection($this->schedules);
            }),
            'students' => $this->relationLoaded('userCourseBlocks')
                ? $this->userCourseBlocks->filter(function ($u) use ($instructor) {
                    if ($instructor && (string) $u->user_id === (string) $instructor->user_id) {
                        return false;
                    }
                    if ($u->relationLoaded('user') && $u->user) {
                        if ($u->user->instructor !== null || strcasecmp((string) $u->user->role_name, 'instructor') === 0) {
                            return false;
                        }
                    }
                    $rawId = (string) $u->user_id;
                    if (str_starts_with(strtoupper($rawId), 'C-')) {
                        return true;
                    }
                    return strcasecmp((string) ($u->user?->role_name ?? ''), 'instructor') !== 0;
                })->map(function ($u) {
                    $user = $u->user;
                    $studentModel = $user?->student instanceof \Illuminate\Support\Collection
                        ? $user->student->first()
                        : $user?->student;

                    return [
                        'user_id' => $u->user_id,
                        'first_name' => $user?->first_name,
                        'last_name' => $user?->last_name,
                        'name' => $user ? trim("{$user->first_name} {$user->last_name}") : null,
                        'sex' => $user?->sex,
                        'email' => $user?->email,
                        'program' => $studentModel?->program?->program_name ?? $studentModel?->program?->name ?? null,
                        'image' => $user?->userProfile?->imagelink ?? $user?->image,
                        'assigned_at' => $u->assigned_at,
                    ];
                })->values()
                : [],
            'user_course_blocks' => $this->whenLoaded('userCourseBlocks'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
