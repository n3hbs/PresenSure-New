<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'course_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'subject_code',
        'name',
    ];

    public function courseBlocks()
    {
        return $this->hasMany(CourseBlock::class, 'course_id', 'course_id');
    }

    public function attendancePolicyCourses()
    {
        return $this->hasMany(AttendancePolicyCourse::class, 'course_id', 'course_id');
    }

    public function attendancePolicies()
    {
        return $this->belongsToMany(AttendancePolicy::class, 'attendance_policy_courses', 'course_id', 'attendance_policy_id')
            ->withPivot('assigned_at');
    }
}
