<?php

namespace App\Http\Requests\Room;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreateRoomRequest extends FormRequest
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
            'building_id' => 'required|integer|exists:buildings,building_id',
            'name' => 'required|string|max:255',
            'floor_no' => 'required|integer|min:0',
            'capacity' => 'nullable|integer|min:1',
            'status' => 'nullable|in:Active,Inactive',
        ];
    }
}
