<?php

namespace App\Http\Resources\Schedule;

use App\Http\Resources\BaseResource;
use App\Http\Resources\CourseBlockResource;
use Illuminate\Http\Request;

class UserCourseScheduleResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [
            'course_block' => new CourseBlockResource(
                $this->whenLoaded('courseBlock')
            ),
        ];
    }
}
