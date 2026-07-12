<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    use HasFactory;

    protected $primaryKey = 'period_id';

    protected $fillable = [
        'semester_id',
        'name',
        'description',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function attendanceSessions()
    {
        return $this->hasMany(AttendanceSession::class, 'period_id', 'period_id');
    }
}
