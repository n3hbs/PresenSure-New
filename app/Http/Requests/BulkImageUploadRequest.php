<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class BulkImageUploadRequest extends FormRequest
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
            'images' => 'required|array|min:1|max:100',
            'images.*' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120',
            'type' => 'nullable|string|in:student,instructor,both,all',
            'overwrite' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Please select at least one image file to upload.',
            'images.array' => 'Images must be provided as a list of files.',
            'images.max' => 'You can upload a maximum of 100 images at once.',
            'images.*.image' => 'Each uploaded file must be a valid image.',
            'images.*.mimes' => 'Images must be in JPG, JPEG, PNG, or WEBP format.',
            'images.*.max' => 'Each image must not exceed 5MB.',
        ];
    }
}
