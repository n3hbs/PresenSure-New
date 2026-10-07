<?php

declare(strict_types=1);

namespace App\Http\Requests\Semester;

use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSchoolYearRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('semesters.manage')
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
            'school_year_start' => ['required', 'date'],
            'school_year_end' => ['required', 'date', 'after:school_year_start'],
        ];
    }

    /**
     * Custom validation rules: prevent duplicate or overlapping school years.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $start = Carbon::parse($this->input('school_year_start'))->startOfDay();
            $end = Carbon::parse($this->input('school_year_end'))->startOfDay();

            $existing = SchoolYear::where(function ($query) use ($start, $end) {
                $query->whereDate('school_year_start', '<=', $end->toDateString())
                    ->whereDate('school_year_end', '>=', $start->toDateString());
            })->first();

            if ($existing) {
                $validator->errors()->add(
                    'school_year_start',
                    "The selected date range overlaps with an existing academic year ({$existing->year_range})."
                );
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_year_start.required' => 'The school year start date is required.',
            'school_year_start.date' => 'The school year start date must be a valid date.',
            'school_year_end.required' => 'The school year end date is required.',
            'school_year_end.date' => 'The school year end date must be a valid date.',
            'school_year_end.after' => 'The school year end date must be after the start date.',
        ];
    }
}
