<?php

namespace App\Http\Requests\Instructor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateInstructorRequest extends FormRequest
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'user_id' => 'required|string|unique:users,user_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[a-zA-Z]+$/'],
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|in:male,female',
            'department_id' => 'required',
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
            'user_id' => 'instructor ID',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'middle_initial' => 'middle initial',
            'sex' => 'sex',
            'department_id' => 'department',
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
            'user_id.unique' => 'This instructor ID is already registered in the system.',
            'user_id.required' => 'Instructor ID is required.',
            'middle_initial.regex' => 'The middle initial must not contain any special characters or periods.',
        ];
    }
}
