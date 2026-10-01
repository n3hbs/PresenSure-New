<?php

declare(strict_types=1);

namespace App\Http\Requests\Building;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreBuildingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('facilities.manage')
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
            'code' => ['required', 'string', 'max:50', 'unique:buildings,code'],
            'name' => ['required', 'string', 'max:255'],
            'rooms' => ['nullable', 'array'],
            'rooms.*.name' => ['required_with:rooms', 'string', 'max:255'],
            'rooms.*.floor_no' => ['required_with:rooms', 'integer', 'min:0'],
            'rooms.*.capacity' => ['nullable', 'integer', 'min:1'],
            'rooms.*.status' => ['nullable', 'in:Active,Inactive'],
        ];
    }

    /**
     * Custom validation rules: check for duplicate room names within the request.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $rooms = $this->input('rooms', []);
            if (! is_array($rooms) || empty($rooms)) {
                return;
            }

            $names = [];
            foreach ($rooms as $index => $room) {
                $name = strtolower(trim((string) ($room['name'] ?? '')));
                if (! empty($name)) {
                    if (in_array($name, $names, true)) {
                        $validator->errors()->add(
                            "rooms.{$index}.name",
                            "Duplicate room name '{$room['name']}' within the submission."
                        );
                    }
                    $names[] = $name;
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
            'code.required' => 'The building code is required.',
            'code.unique' => 'This building code is already in use.',
            'name.required' => 'The building name is required.',
            'rooms.*.name.required_with' => 'Each room must have a name.',
            'rooms.*.floor_no.required_with' => 'Each room must specify a floor number.',
        ];
    }
}
