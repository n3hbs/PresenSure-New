<?php

declare(strict_types=1);

namespace App\Http\Requests\Department;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreDepartmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('departments.manage')
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
            'department_code' => ['required', 'string', 'max:20', 'unique:departments,department_code'],
            'department_name' => ['required', 'string', 'max:255', 'unique:departments,department_name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'programs' => ['nullable', 'array'],
            'programs.*.program_code' => ['required_with:programs', 'string', 'max:20'],
            'programs.*.program_name' => ['required_with:programs', 'string', 'max:255'],
            'programs.*.program_years' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    /**
     * Custom validation rules: check for duplicate program codes within the request.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $programs = $this->input('programs', []);
            if (! is_array($programs) || empty($programs)) {
                return;
            }

            $codes = [];
            foreach ($programs as $index => $program) {
                $code = strtoupper(trim((string) ($program['program_code'] ?? '')));
                if (! empty($code)) {
                    if (in_array($code, $codes, true)) {
                        $validator->errors()->add(
                            "programs.{$index}.program_code",
                            "Duplicate program code '{$code}' within the submission."
                        );
                    }
                    $codes[] = $code;
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
            'department_code.required' => 'The department code is required.',
            'department_code.unique' => 'This department code is already in use.',
            'department_name.required' => 'The department name is required.',
            'department_name.unique' => 'A department with this name already exists.',
            'programs.*.program_code.required_with' => 'Each program must have a program code.',
            'programs.*.program_name.required_with' => 'Each program must have a program name.',
        ];
    }
}
