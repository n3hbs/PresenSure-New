<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleWithPermissionsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'role_id' => $this->role_id,
            'role_name' => $this->role_name,
            'is_system_admin' => (bool) $this->is_system_admin,
            'description' => $this->description,
            'user_role_count' => $this->user_role_count ?? $this->userRole()->count(),
            'permissions' => PermissionResource::collection($this->whenLoaded('permissions')),
        ];
    }
}
