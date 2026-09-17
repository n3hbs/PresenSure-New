<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRolePermissionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'integer|exists:permissions,permission_id',
        ];
    }

    public function messages(): array
    {
        return [
            'permission_ids.present' => 'The permission_ids field must be present.',
            'permission_ids.array' => 'The permission_ids must be an array.',
            'permission_ids.*.exists' => 'One or more selected permissions are invalid.',
        ];
    }
}
