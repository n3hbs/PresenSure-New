<?php

namespace App\Http\Requests\Period;

use App\Models\Period;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreatePeriodRequest extends FormRequest
{
    private const PERIOD_ORDER = ['prelim', 'midterm', 'prefinals', 'finals'];

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
            'semester_id' => ['required', 'integer', 'exists:semesters,semester_id'],
            'name' => [
                'required',
                'string',
                Rule::in(self::PERIOD_ORDER),
                Rule::unique('periods', 'name')->where('semester_id', $this->input('semester_id')),
            ],
            'description' => ['nullable', 'string', 'max:255'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $context = $this->periodValidationContext();

            if (!$context) {
                return;
            }

            $this->validatePeriodWithinSemester($validator, $context);
            $this->validateNoOverlappingPeriod($validator, $context);
            $this->validatePreviousPeriodsExist($validator, $context);
            $this->validatePeriodSequenceDates($validator, $context);
        });
    }

    private function periodValidationContext(): ?array
    {
        $semester = Semester::where('semester_id', $this->input('semester_id'))->first();

        if (!$semester) {
            return null;
        }

        $periodName = $this->input('name');

        return [
            'semester' => $semester,
            'existingPeriods' => Period::where('semester_id', $semester->semester_id)->get(),
            'periodName' => $periodName,
            'periodIndex' => array_search($periodName, self::PERIOD_ORDER, true),
            'periodStart' => Carbon::parse($this->input('period_start'))->startOfDay(),
            'periodEnd' => Carbon::parse($this->input('period_end'))->startOfDay(),
            'semesterStart' => Carbon::parse($semester->semester_start)->startOfDay(),
            'semesterEnd' => Carbon::parse($semester->semester_end)->startOfDay(),
        ];
    }

    private function validatePeriodWithinSemester(Validator $validator, array $context): void
    {
        if (
            $context['periodStart']->lt($context['semesterStart']) ||
            $context['periodEnd']->gt($context['semesterEnd'])
        ) {
            $validator->errors()->add(
                'period_start',
                'The period dates must be within the selected semester dates.'
            );
        }
    }

    private function validateNoOverlappingPeriod(Validator $validator, array $context): void
    {
        $overlappingPeriod = $context['existingPeriods']
            ->first(function (Period $period) use ($context) {
                $existingStart = Carbon::parse($period->period_start)->startOfDay();
                $existingEnd = Carbon::parse($period->period_end)->startOfDay();

                return $existingStart->lte($context['periodEnd']) &&
                    $existingEnd->gte($context['periodStart']);
            });

        if ($overlappingPeriod) {
            $validator->errors()->add(
                'period_start',
                "The period dates overlap with the existing {$overlappingPeriod->name} period."
            );
        }
    }

    private function validatePreviousPeriodsExist(Validator $validator, array $context): void
    {
        $previousPeriodNames = array_slice(self::PERIOD_ORDER, 0, $context['periodIndex']);

        $missingPreviousPeriod = collect($previousPeriodNames)
            ->first(fn (string $name) => !$context['existingPeriods']->contains('name', $name));

        if ($missingPreviousPeriod) {
            $validator->errors()->add(
                'name',
                "Create the {$missingPreviousPeriod} period before creating {$context['periodName']}."
            );
        }
    }

    private function validatePeriodSequenceDates(Validator $validator, array $context): void
    {
        $previousPeriod = $this->adjacentPeriod($context, -1);
        $nextPeriod = $this->adjacentPeriod($context, 1);

        if ($previousPeriod && Carbon::parse($previousPeriod->period_end)->startOfDay()->gte($context['periodStart'])) {
            $validator->errors()->add(
                'period_start',
                "The {$context['periodName']} period must start after the {$previousPeriod->name} period ends."
            );
        }

        if ($nextPeriod && Carbon::parse($nextPeriod->period_start)->startOfDay()->lte($context['periodEnd'])) {
            $validator->errors()->add(
                'period_end',
                "The {$context['periodName']} period must end before the {$nextPeriod->name} period starts."
            );
        }
    }

    private function adjacentPeriod(array $context, int $direction): ?Period
    {
        $adjacentIndex = $context['periodIndex'] + $direction;

        if (!isset(self::PERIOD_ORDER[$adjacentIndex])) {
            return null;
        }

        return $context['existingPeriods']->firstWhere('name', self::PERIOD_ORDER[$adjacentIndex]);
    }
}
