<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Semester extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'semester_id';

    protected $fillable = [
        'school_year_id',
        'term',
        'semester_start',
        'semester_end',
        'is_active',
        'status',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'semester_start' => 'date',
        'semester_end' => 'date',
    ];

    protected $appends = [
        'computed_status',
        'active_period',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id', 'school_year_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'semester_id', 'semester_id');
    }

    /**
     * Backward-compatible alias for student relation.
     */
    public function student(): HasMany
    {
        return $this->students();
    }

    public function courseBlocks(): HasMany
    {
        return $this->hasMany(CourseBlock::class, 'semester_id', 'semester_id');
    }

    public function periods(): HasMany
    {
        return $this->hasMany(Period::class, 'semester_id', 'semester_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'semester_id', 'semester_id');
    }

    /**
     * Determine if this semester is active based on current date alignment.
     */
    public function getIsActiveAttribute($value): bool
    {
        $today = Carbon::today();
        $start = $this->semester_start ? Carbon::parse($this->semester_start)->startOfDay() : null;
        $end = $this->semester_end ? Carbon::parse($this->semester_end)->endOfDay() : null;

        if ($start && $end) {
            if ($today->betweenIncluded($start, $end)) {
                return true;
            }

            if ($today->gt($end)) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * Determine real-time semester status based on date alignment.
     */
    public function getComputedStatusAttribute(): string
    {
        $today = Carbon::today();
        $start = $this->semester_start ? Carbon::parse($this->semester_start)->startOfDay() : null;
        $end = $this->semester_end ? Carbon::parse($this->semester_end)->endOfDay() : null;

        if ($start && $today->lt($start)) {
            return 'Upcoming';
        }

        if ($end && $today->gt($end)) {
            return 'Completed';
        }

        return 'Active';
    }

    /**
     * Get the period currently active (date aligned with today).
     */
    public function getActivePeriodAttribute(): ?Period
    {
        $today = Carbon::today();
        $periods = $this->relationLoaded('periods') ? $this->periods : $this->periods()->get();

        return $periods->first(function (Period $period) use ($today) {
            $start = $period->period_start ? Carbon::parse($period->period_start)->startOfDay() : null;
            $end = $period->period_end ? Carbon::parse($period->period_end)->endOfDay() : null;

            return $start && $end && $today->betweenIncluded($start, $end);
        });
    }
}
