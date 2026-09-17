<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateStudentRequest extends FormRequest
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
        $isExisting = $this->input('registration_type') === 'existing';

        return [
            'registration_type' => 'nullable|string|in:new,existing',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'user_id' => $isExisting
                ? 'required|string|exists:users,user_id'
                : 'required|string|unique:users,user_id',
            'first_name' => $isExisting ? 'nullable|string|max:255' : 'required|string|max:255',
            'last_name' => $isExisting ? 'nullable|string|max:255' : 'required|string|max:255',
            'middle_initial' => ['nullable', 'string', 'max:5', 'regex:/^[a-zA-Z]+$/'],
            'suffix' => 'nullable|string|max:10',
            'sex' => $isExisting ? 'nullable|in:male,female' : 'required|in:male,female',
            'program_id' => 'required',
            'year' => 'required|string|max:50',
            'block' => 'required|string|max:50',
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
            'user_id' => 'student number',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'middle_initial' => 'middle initial',
            'sex' => 'sex',
            'program_id' => 'program',
            'year' => 'year level',
            'block' => 'block',
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
            'user_id.unique' => 'This student number is already registered in the system.',
            'user_id.exists' => 'No account found with this student number.',
            'user_id.required' => 'Student number is required.',
            'middle_initial.regex' => 'The middle initial must not contain any special characters or periods.',
        ];
    }
}
