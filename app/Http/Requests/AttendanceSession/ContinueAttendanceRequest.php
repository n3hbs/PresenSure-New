<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContinueAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'attendance_session_id' => 'required|integer|exists:attendance_sessions,attendance_session_id',
            'schedule_id' => 'required|integer|exists:schedules,schedule_id',
            'device_id' => 'required|string|max:255',
        ];
    }
}
