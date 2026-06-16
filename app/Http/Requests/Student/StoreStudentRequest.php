<?php

namespace App\Http\Requests\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class StoreStudentRequest extends FormRequest
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
        /**
        $validator = Validator::make($request->all(), [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'user_id' => 'required|string', // Changed from 'id' to 'user_id'
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middle_initial' => 'nullable|string|max:5',
            'suffix' => 'nullable|string|max:10',
            'sex' => 'required|in:Male,Female',
            'program' => 'required|string|max:255',
            'year' => 'required|string|max:50',
            'block' => 'required|string|max:50',
        ]);
        */
        return [
            'user_id' => 'required|string',
            'first_name' => '',
            'middle_initial' => '', 
            'last_name' => '',
            'program'
        ];
    }
}
