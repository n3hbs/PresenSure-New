<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\CourseBlock;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCourseBlockRequest extends FormRequest
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
            || (method_exists($user, 'can') && $user->can('courses.manage'))
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $blockId = $this->route('id') ?? $this->route('course_block');
        $block = $blockId ? CourseBlock::find($blockId) : null;
        $courseId = $this->input('course_id', $block?->course_id);
        $semesterId = $this->input('semester_id', $block?->semester_id);

        return [
            'block_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('course_blocks', 'block_code')
                    ->where(function ($query) use ($courseId, $semesterId) {
                        return $query->where('course_id', $courseId)
                            ->where('semester_id', $semesterId)
                            ->whereNull('deleted_at');
                    })
                    ->ignore($blockId, 'course_block_id'),
            ],
            'instructor_id' => [
                'nullable',
                Rule::exists('users', 'user_id'),
            ],
            'course_id' => ['nullable', 'integer', 'exists:courses,course_id'],
            'semester_id' => ['nullable', 'integer', 'exists:semesters,semester_id'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'block_code.unique' => 'The block code is already in use by another section in this semester.',
            'instructor_id.exists' => 'The selected instructor does not exist.',
        ];
    }
}
