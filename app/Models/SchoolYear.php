<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
{
    use HasFactory;

    protected $primaryKey = 'school_year_id';

    protected $fillable = [
        'school_year_start',
        'school_year_end',
    ];

    protected $casts = [
        'school_year_start' => 'date',
        'school_year_end' => 'date',
    ];

    protected $appends = [
        'year_range',
    ];

    public function semesters(): HasMany
    {
        return $this->hasMany(Semester::class, 'school_year_id', 'school_year_id');
    }

    public function semester(): HasMany
    {
        return $this->semesters();
    }

    public function getYearRangeAttribute(): string
    {
        if (! $this->school_year_start || ! $this->school_year_end) {
            return 'N/A';
        }

        $startYear = Carbon::parse($this->school_year_start)->format('Y');
        $endYear = Carbon::parse($this->school_year_end)->format('Y');

        return "{$startYear} - {$endYear}";
    }
}
