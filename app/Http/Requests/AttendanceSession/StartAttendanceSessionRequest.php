<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StartAttendanceSessionRequest extends FormRequest
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
            'schedule_id' => 'required|integer|exists:schedules,schedule_id',
            'period_id' => 'required|integer|exists:periods,period_id',
            'verification_mode' => 'required|string|in:ble,face,ble_face',
            'ble_source_type' => 'required|string|in:none,instructor_phone,room_beacon',
            'beacon_id' => 'required_if:ble_source_type,room_beacon|nullable|string|max:255',
            'requires_periodic_verification' => 'sometimes|boolean',
        ];
    }
}
