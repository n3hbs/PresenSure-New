<?php

namespace App\Http\Resources\Student;

use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckStudentResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource['data']['user'] ?? $this->resource['user'] ?? null;

        return [
            'success' => $this->resource['success'] ?? false,
            'exists' => $this->resource['exists'] ?? false,
            'message' => $this->resource['message'] ?? null,
            'data' => $user ? [
                ...((new UserResource($user))->toArray($request)),
                'profile' => $user->userProfile
                    ? new UserProfileResource($user->userProfile)
                    : null,
            ] : [],
        ];
    }
}
