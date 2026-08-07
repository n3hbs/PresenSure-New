<?php

namespace App\Http\Resources\Instructor;

use App\Http\Resources\DepartmentResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $instructor = $this->whenLoaded('instructor');

        return [
            'user' => new UserResource($this),
            'instructor' => $instructor ? [
                'instructor_id' => $instructor->instructor_id,
                'user_id' => $instructor->user_id,
                'department_id' => $instructor->department_id,
                'status' => $instructor->status ?? 'Active',
                'department' => $instructor->relationLoaded('department') && $instructor->department
                    ? new DepartmentResource($instructor->department)
                    : null,
            ] : null,
            'role' => new RoleResource(
                $this->whenLoaded('roleAssignment')?->role
            ),
            'profile' => new UserProfileResource(
                $this->whenLoaded('userProfile')
            ),
        ];
    }
}
