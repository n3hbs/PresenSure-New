<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Foundation\Http\FormRequest;

class CreateDeviceAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'integer', 'exists:schedules,schedule_id'],
            'device_id' => ['required', 'string', 'max:255'],
            'device_room_id' => ['required', 'integer', 'exists:rooms,room_id'],
            'attendance_mode' => ['required', 'string', 'in:ble,face,ble_and_face'],
            'continuous_checking' => ['required', 'boolean'],
            'requested_duration_minutes' => ['required', 'integer', 'min:1', 'max:1440'],
        ];
    }
}
