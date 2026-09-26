<?php

declare(strict_types=1);

namespace App\Http\Requests\Semester;

use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSemesterRequest extends FormRequest
{
    private const PERIOD_ORDER = ['prelim', 'midterm', 'prefinals', 'finals'];

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
            'school_year_id' => ['required', 'integer', 'exists:school_years,school_year_id'],
            'term' => ['required', 'string', 'in:First Semester,Second Semester,Summer'],
            'semester_start' => ['required', 'date'],
            'semester_end' => ['required', 'date', 'after:semester_start', 'after_or_equal:today'],
            'is_active' => ['nullable', 'boolean'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'periods' => ['nullable', 'array'],
            'periods.*.name' => ['required', 'string', 'in:prelim,midterm,prefinals,finals'],
            'periods.*.period_start' => ['required', 'date'],
            'periods.*.period_end' => ['required', 'date', 'after_or_equal:periods.*.period_start'],
            'periods.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation rules: duplication checks and period sequence.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $schoolYearId = (int) $this->input('school_year_id');
            $term = $this->input('term');
            $start = Carbon::parse($this->input('semester_start'))->startOfDay();
            $end = Carbon::parse($this->input('semester_end'))->startOfDay();

            // 1. Avoid semester name / term duplication in the same school year
            $duplicateTerm = Semester::where('school_year_id', $schoolYearId)
                ->where('term', $term)
                ->exists();

            if ($duplicateTerm) {
                $validator->errors()->add(
                    'term',
                    "A semester for '{$term}' already exists in this school year."
                );
            }

            // 2. Avoid overlapping semester dates in the same school year
            $overlappingSemester = Semester::where('school_year_id', $schoolYearId)
                ->where(function ($query) use ($start, $end) {
                    $query->whereDate('semester_start', '<=', $end->toDateString())
                        ->whereDate('semester_end', '>=', $start->toDateString());
                })
                ->first();

            if ($overlappingSemester) {
                $validator->errors()->add(
                    'semester_start',
                    "The semester dates overlap with the existing '{$overlappingSemester->term}' in this school year."
                );
            }

            // 3. Period sequence validation if periods are provided
            $periods = $this->input('periods');
            if (! empty($periods) && is_array($periods)) {
                $this->validatePeriodsSequence($validator, $periods, $start, $end);
            }
        });
    }

    private function validatePeriodsSequence(Validator $validator, array $periods, Carbon $semesterStart, Carbon $semesterEnd): void
    {
        $names = array_column($periods, 'name');

        if (count($names) !== count(array_unique($names))) {
            $validator->errors()->add('periods', 'Periods must not contain duplicate period names.');

            return;
        }

        $expectedNames = array_slice(self::PERIOD_ORDER, 0, count($names));
        if ($names !== $expectedNames) {
            $validator->errors()->add(
                'periods',
                'Periods must be created in chronological order: Prelim, then Midterm, then Prefinals, then Finals.'
            );

            return;
        }

        $prevEnd = null;
        $prevName = null;

        foreach ($periods as $index => $period) {
            $pStart = Carbon::parse($period['period_start'])->startOfDay();
            $pEnd = Carbon::parse($period['period_end'])->startOfDay();
            $pName = $period['name'];

            if ($pStart->lt($semesterStart) || $pEnd->gt($semesterEnd)) {
                $validator->errors()->add(
                    "periods.{$index}.period_start",
                    "The {$pName} period dates must be within the semester dates ({$semesterStart->toDateString()} to {$semesterEnd->toDateString()})."
                );
            }

            if ($prevEnd !== null && $pStart->lt($prevEnd)) {
                $validator->errors()->add(
                    "periods.{$index}.period_start",
                    "The {$pName} period must start on or after the day {$prevName} ends."
                );
            }

            $prevEnd = $pEnd;
            $prevName = $pName;
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'school_year_id.required' => 'The school year is required.',
            'school_year_id.exists' => 'The selected school year does not exist.',
            'term.required' => 'The term is required.',
            'term.in' => 'The term must be First Semester, Second Semester, or Summer.',
            'semester_start.required' => 'The start date is required.',
            'semester_start.date' => 'The start date must be a valid date.',
            'semester_end.required' => 'The end date is required.',
            'semester_end.date' => 'The end date must be a valid date.',
            'semester_end.after' => 'The semester end date must be after the start date.',
            'semester_end.after_or_equal' => 'The semester end date cannot be in the past. It must be today or an upcoming date.',
            'remarks.max' => 'Remarks may not exceed 500 characters.',
        ];
    }
}
