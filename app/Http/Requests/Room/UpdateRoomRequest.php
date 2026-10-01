<?php

declare(strict_types=1);

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
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
            'building_id' => ['sometimes', 'required', 'integer', 'exists:buildings,building_id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'floor_no' => ['sometimes', 'required', 'integer', 'min:0'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:Active,Inactive'],
        ];
    }

    /**
     * Custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'building_id.exists' => 'The selected building does not exist.',
            'name.required' => 'The room name is required.',
            'floor_no.min' => 'Floor number cannot be negative.',
            'capacity.min' => 'Capacity must be at least 1.',
        ];
    }
}
