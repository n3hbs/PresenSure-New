<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RoleResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this['user'];
        $role = $user->relationLoaded('roleAssignment') && $user->roleAssignment
            ? $user->roleAssignment->role
            : null;

        return [
            'token' => $this['token'],
            'user' => [
                ...(new UserResource($user))->toArray($request),
                'role' => $role ? new RoleResource($role) : null,
            ],
        ];
    }
}
