<?php

namespace App\Http\Resources\Student;

use App\Http\Resources\BaseResource;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class CheckStudentResource extends BaseResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        $user = $this->resource['data']['user'] ?? $this->resource['user'] ?? null;

        return [
            'exists' => $this->resource['exists'] ?? ($user !== null),
            'already_enrolled' => $this->resource['already_enrolled'] ?? false,
            'user' => $user ? [
                ...((new UserResource($user))->toArray($request)),
                'profile' => $user->userProfile
                    ? new UserProfileResource($user->userProfile)
                    : null,
            ] : null,
        ];
    }
}
