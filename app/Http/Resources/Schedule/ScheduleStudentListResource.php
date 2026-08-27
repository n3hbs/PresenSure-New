<?php

namespace App\Http\Resources\Schedule;

use App\Http\Resources\BaseResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\StudentResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class ScheduleStudentListResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [
            'students' => $this->resource['students']->map(function ($user) use ($request) {
                return [
                    'user'    => new UserResource($user),
                    'student' => StudentResource::collection($user->student),
                    'role'    => new RoleResource($user->roleAssignment?->role),
                    'profile' => $user->userProfile ? new UserProfileResource($user->userProfile) : null,
                ];
            }),
            'student_count' => $this->resource['student_count'],
            'students_without_profile_image_count' => $this->resource['students_without_profile_image_count'],
        ];
    }
}
