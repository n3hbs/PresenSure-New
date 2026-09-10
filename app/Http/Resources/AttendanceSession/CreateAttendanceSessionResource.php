<?php

namespace App\Http\Resources\AttendanceSession;

use App\Http\Resources\AttendanceSessionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CreateAttendanceSessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'session' => new AttendanceSessionResource($this['session']),
            'ble_token' => $this['ble_token'],
            'beacon_configuration' => $this['beacon_configuration'],
        ];
    }
}
