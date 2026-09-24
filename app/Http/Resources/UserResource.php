<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'middle_initial' => $this->middle_initial,
            'last_name' => $this->last_name,
            'suffix' => $this->suffix,
            'sex' => $this->sex,
            'full_name' => trim("{$this->first_name} ".($this->middle_initial ? "{$this->middle_initial} " : '')."{$this->last_name} {$this->suffix}"),
            'profile_picture' => $this->userProfile?->profile_picture,
            'role_name' => $this->role_name,
            'permissions' => $this->permissions,
        ];
    }
}
