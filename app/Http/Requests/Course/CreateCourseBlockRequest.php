<?php

namespace App\Http\Requests\Course;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCourseBlockRequest extends FormRequest
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
            'course_id' => ['required', 'integer', 'exists:courses,course_id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,semester_id'],
            'block_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('course_blocks', 'block_code')
                    ->where('course_id', $this->input('course_id'))
                    ->where('semester_id', $this->input('semester_id'))
                    ->whereNull('deleted_at'),
            ],
        ];
    }
}
