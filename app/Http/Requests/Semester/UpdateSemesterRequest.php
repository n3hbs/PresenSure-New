<?php

declare(strict_types=1);

namespace App\Http\Requests\Semester;

use App\Models\Period;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateSemesterRequest extends FormRequest
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
            'school_year_id' => ['sometimes', 'required', 'integer', 'exists:school_years,school_year_id'],
            'term' => ['sometimes', 'required', 'string', 'in:First Semester,Second Semester,Summer'],
            'semester_start' => ['sometimes', 'required', 'date'],
            'semester_end' => ['sometimes', 'required', 'date', 'after:semester_start'],
            'is_active' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'in:active,upcoming,completed,inactive'],
            'remarks' => ['nullable', 'string', 'max:500'],
            'periods' => ['nullable', 'array'],
            'periods.*.period_id' => ['nullable', 'integer'],
            'periods.*.name' => ['required', 'string', 'in:prelim,midterm,prefinals,finals'],
            'periods.*.period_start' => ['required', 'date'],
            'periods.*.period_end' => ['required', 'date', 'after_or_equal:periods.*.period_start'],
            'periods.*.description' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom validation rules: duplication, overlap, and period sequence checks.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $semesterId = (int) $this->route('semester_id');
            $currentSemester = Semester::find($semesterId);

            if (! $currentSemester) {
                return;
            }

            $schoolYearId = (int) ($this->input('school_year_id') ?? $currentSemester->school_year_id);
            $term = $this->input('term') ?? $currentSemester->term;
            $startStr = $this->input('semester_start') ?? $currentSemester->semester_start;
            $endStr = $this->input('semester_end') ?? $currentSemester->semester_end;

            $start = Carbon::parse($startStr)->startOfDay();
            $end = Carbon::parse($endStr)->startOfDay();

            // 1. Avoid duplicate term in same school year (excluding this semester)
            $duplicateTerm = Semester::where('school_year_id', $schoolYearId)
                ->where('term', $term)
                ->where('semester_id', '!=', $semesterId)
                ->exists();

            if ($duplicateTerm) {
                $validator->errors()->add(
                    'term',
                    "A semester for '{$term}' already exists in this school year."
                );
            }

            // 2. Avoid overlapping semester dates in same school year (excluding this semester)
            $overlappingSemester = Semester::where('school_year_id', $schoolYearId)
                ->where('semester_id', '!=', $semesterId)
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

                // Prevent removal of existing periods that have attendance sessions recorded
                $existingPeriods = Period::where('semester_id', $semesterId)->get();
                $submittedNames = array_column($periods, 'name');
                foreach ($existingPeriods as $existingPeriod) {
                    if (! in_array($existingPeriod->name, $submittedNames, true)) {
                        if ($existingPeriod->attendanceSessions()->exists()) {
                            $validator->errors()->add(
                                'periods',
                                "Cannot remove period '{$existingPeriod->name}' because attendance sessions have already been recorded for it."
                            );
                        }
                    }
                }
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
                'Periods must be defined in chronological order: Prelim, then Midterm, then Prefinals, then Finals.'
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
            'school_year_id.exists' => 'The selected school year does not exist.',
            'term.in' => 'The term must be First Semester, Second Semester, or Summer.',
            'semester_end.after' => 'The semester end date must be after the start date.',
            'remarks.max' => 'Remarks may not exceed 500 characters.',
        ];
    }
}
