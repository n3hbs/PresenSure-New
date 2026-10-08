<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'course_block_id';

    protected $fillable = [
        'course_id',
        'semester_id',
        'block_code',
        'instructor_id',
    ];

    protected $casts = [
        'course_block_id' => 'integer',
        'course_id' => 'integer',
        'semester_id' => 'integer',
        'instructor_id' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * Parent Academic Course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Academic Semester
     */
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    /**
     * Assigned Instructor
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id', 'user_id');
    }

    /**
     * Linked Class Schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'course_block_id', 'course_block_id');
    }

    /**
     * Linked User Course Blocks (Students & Instructors)
     */
    public function userCourseBlocks(): HasMany
    {
        return $this->hasMany(UserCourseBlock::class, 'course_block_id', 'course_block_id');
    }
}
