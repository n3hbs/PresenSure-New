<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'course_block_id';

    protected $fillable = [
        'course_id',
        'semester_id',
        'block_code',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'course_block_id', 'course_block_id');
    }
}
