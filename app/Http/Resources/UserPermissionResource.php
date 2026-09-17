<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserPermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this['user_id'],
            'full_name' => $this['full_name'],
            'role_name' => $this['role_name'],
            'inherited_permission_ids' => $this['inherited_permission_ids'],
            'direct_permission_ids' => $this['direct_permission_ids'],
            'effective_permission_ids' => $this['effective_permission_ids'],
            'effective_permission_names' => $this['effective_permission_names'],
        ];
    }
}
