<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'student_id' => $this->student_id,
            'user_id' => $this->user_id,
            'year' => $this->year,
            'block' => $this->block,
            'status' => $this->status,
            'program' => new ProgramResource(
                $this->whenLoaded('program')
            ),
        ];
    }
}
