<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'program_id' => $this->program_id,
            'program_code' => $this->program_code,
            'program_name' => $this->program_name,

            'department' => new DepartmentResource(
                $this->whenLoaded('department')
            ),
        ];
    }
}
