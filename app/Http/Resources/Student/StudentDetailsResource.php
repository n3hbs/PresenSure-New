<?php

namespace App\Http\Resources\Student;

use App\Http\Resources\BaseResource;
use App\Http\Resources\RoleResource;
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

        ];
    }
}
