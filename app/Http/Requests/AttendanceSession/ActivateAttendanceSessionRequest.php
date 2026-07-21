<?php

namespace App\Http\Requests\AttendanceSession;

use Illuminate\Foundation\Http\FormRequest;

class ActivateAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'device_id' => ['required', 'string', 'max:255'],
            'advertising' => ['required', 'accepted'],
            'device_started_at' => ['required', 'integer', 'min:1'],
        ];
    }
}
