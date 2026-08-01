<?php

namespace App\Http\Requests\AttendanceRecord;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRecordRequest extends FormRequest
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
            'schedule_id' => 'required',
            'presence_verified' => 'required',
            'face_verified' => 'required',
            'face_verified_at' => 'required',
            'verified_at' => 'required',
            'rssi' => 'required',
            'detected_at' => 'required',
        ];
    }
}
