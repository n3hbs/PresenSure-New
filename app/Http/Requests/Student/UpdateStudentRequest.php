<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('middle_initial') && is_string($this->middle_initial)) {
            $cleaned = preg_replace('/[^a-zA-Z]/', '', $this->middle_initial);
            $this->merge([
                'middle_initial' => $cleaned !== '' ? strtoupper($cleaned) : null,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|nullable|string|exists:users,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[a-zA-Z]+$/'],
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|in:male,female',
            'program_id' => 'required|exists:programs,program_id',
            'year' => 'required|string|max:50',
            'block' => 'required|string|max:50',
            'status' => 'nullable|in:Active,Inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }


    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'middle_initial' => 'middle initial',
            'suffix' => 'suffix',
            'sex' => 'sex',
            'program_id' => 'program',
            'year' => 'year level',
            'block' => 'block',
            'status' => 'status',
            'image' => 'profile photo',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'middle_initial.regex' => 'The middle initial must not contain any special characters or periods.',
            'sex.required' => 'Please select the student sex.',
            'program_id.required' => 'Please select a program.',
            'program_id.exists' => 'The selected program does not exist.',
            'year.required' => 'Year level is required.',
            'block.required' => 'Block is required.',
            'image.image' => 'The uploaded file must be an image.',
            'image.max' => 'The image size cannot exceed 2MB.',
        ];
    }
}
