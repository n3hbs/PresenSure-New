<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkStudentRequest extends FormRequest
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
            'students' => 'required|array|min:1',
            'students.*.user_id' => 'required|string',
            'students.*.first_name' => 'required|string|max:255',
            'students.*.last_name' => 'required|string|max:255',
            'students.*.middle_initial' => 'nullable|string|max:5',
            'students.*.suffix' => 'nullable|string|max:20',
            'students.*.sex' => 'nullable|string|in:male,female,Male,Female',
            'students.*.program_id' => 'required|integer',
            'students.*.year' => 'required|string|max:50',
            'students.*.block' => 'required|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'students.required' => 'No student records provided for enrollment.',
            'students.min' => 'At least one student record is required to proceed.',
            'students.*.user_id.required' => 'Student ID is required.',
            'students.*.program_id.required' => 'Program is required.',
        ];
    }
}
