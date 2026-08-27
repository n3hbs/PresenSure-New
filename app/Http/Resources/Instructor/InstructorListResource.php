<?php

namespace App\Http\Resources\Instructor;

use App\Http\Resources\BaseResource;
use App\Http\Resources\DepartmentResource;
use App\Http\Resources\InstructorResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorListResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        $instructor = $this->whenLoaded('instructor');

        return [
            'user' => new UserResource($this),
            'instructor' => new InstructorResource($instructor),
            'role' => new RoleResource(
                $this->whenLoaded('roleAssignment')?->role
            ),
            'profile' => new UserProfileResource(
                $this->whenLoaded('userProfile')
            ),
        ];
    }
}
