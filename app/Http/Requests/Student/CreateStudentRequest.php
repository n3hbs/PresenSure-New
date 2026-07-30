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
            'middle_initial' => 'nullable|string|max:5',
            'suffix' => 'nullable|string|max:10',
            'sex' => $isExisting ? 'nullable|in:male,female' : 'required|in:male,female',
            'program_id' => 'required',
            'year' => 'required|string|max:50',
            'block' => 'required|string|max:50',
        ];
    }
}
