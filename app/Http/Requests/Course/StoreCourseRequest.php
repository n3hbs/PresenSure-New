<?php

declare(strict_types=1);

namespace App\Http\Requests\Course;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('courses.manage')
            || $user->hasRole('administrator')
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_code' => ['required', 'string', 'max:20', 'unique:courses,subject_code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'course_blocks' => ['nullable', 'array'],
            'course_blocks.*.block_code' => ['required_with:course_blocks', 'string', 'max:255'],
            'course_blocks.*.semester_id' => ['nullable', 'integer', 'exists:semesters,semester_id'],
        ];
    }

    /**
     * Custom validation rules: check for duplicate block codes within the submission.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $blocks = $this->input('course_blocks', []);
            if (! is_array($blocks) || empty($blocks)) {
                return;
            }

            $seen = [];
            foreach ($blocks as $index => $block) {
                $code = strtoupper(trim((string) ($block['block_code'] ?? '')));
                $semId = $block['semester_id'] ?? 'active';
                $key = "{$semId}:{$code}";

                if (! empty($code)) {
                    if (in_array($key, $seen, true)) {
                        $validator->errors()->add(
                            "course_blocks.{$index}.block_code",
                            "Duplicate course block '{$code}' within the submission."
                        );
                    }
                    $seen[] = $key;
                }
            }
        });
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject_code.required' => 'The subject code is required.',
            'subject_code.unique' => 'This subject code is already in use.',
            'name.required' => 'The course name is required.',
            'course_blocks.*.block_code.required_with' => 'Each course block must have a block code.',
        ];
    }
}
