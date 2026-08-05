<?php

namespace App\Http\Requests\AttendanceRecord;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckAttendanceRecordRequest extends FormRequest
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
            'schedule_id' => 'required_without:attendance_schedule_id|nullable|integer|exists:schedules,schedule_id',
            'attendance_schedule_id' => 'required_without:schedule_id|nullable|integer|exists:schedules,schedule_id',
        ];
    }

}
