<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'instructor_id' => $this->instructor_id,
            'user' => $this->user,
            'department' => new DepartmentResource(
                $this->whenLoaded('department')
            ),
            'status' => $this->status,
        ];
    }
}
