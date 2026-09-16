<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInstructorRequest extends FormRequest
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
            'user_id' => 'sometimes|nullable|string|exists:users,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|in:male,female',
            'department_id' => 'required|exists:departments,department_id',
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
            'department_id' => 'department',
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
            'first_name.required' => 'Please enter the first name.',
            'last_name.required' => 'Please enter the last name.',
            'sex.required' => 'Please select a gender.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'The selected department does not exist.',
        ];
    }
}
