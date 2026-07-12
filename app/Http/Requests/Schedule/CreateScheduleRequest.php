<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateScheduleRequest extends FormRequest
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
            'course_id' => 'required|integer|exists:courses,course_id',
            'room_id' => 'required|integer|exists:rooms,room_id',
            'semester_id' => 'required|integer|exists:semesters,semester_id',
            'block_code' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'days' => ['nullable', 'array', 'min:1'],
            'days.*' => [
                'required',
                'string',
                'distinct',
                Rule::in([
                    'monday',
                    'tuesday',
                    'wednesday',
                    'thursday',
                    'friday',
                    'saturday',
                    'sunday',
                ]),
            ],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['required', 'string', 'distinct', 'exists:users,user_id'],
        ];
    }
}
