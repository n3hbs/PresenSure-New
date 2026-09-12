<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ExtractBulkStudentRequest extends FormRequest
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
            'file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Please select a spreadsheet file to upload.',
            'file.mimes' => 'The file must be an Excel (.xlsx, .xls) or CSV (.csv) spreadsheet.',
            'file.max' => 'The file size must not exceed 5MB.',
        ];
    }
}
