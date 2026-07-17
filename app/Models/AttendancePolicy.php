<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_policy_id';

    protected $fillable = [
        'user_id',
        'policy_name',
        'is_default',
        'calculation_type',
        'late_threshold',
        'absent_threshold',
        'lates_to_absent',
        'consecutive_absents_to_fail',
        'attendance_weight',
        'base_score',
        'absent_detection',
        'late_detection',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'late_threshold' => 'integer',
        'absent_threshold' => 'integer',
        'lates_to_absent' => 'integer',
        'consecutive_absents_to_fail' => 'integer',
        'attendance_weight' => 'decimal:2',
        'base_score' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function attendancePolicyCourses()
    {
        return $this->hasMany(AttendancePolicyCourse::class, 'attendance_policy_id', 'attendance_policy_id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'attendance_policy_courses', 'attendance_policy_id', 'course_id')
            ->withPivot('assigned_at');
    }
}
