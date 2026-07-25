<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'schedule_id';

    protected $fillable = [
        'course_block_id',
        'room_id',
        'semester_id',
        'block_code',
        'schedule_type',
        'start_time',
        'end_time',
    ];

    public function courseBlock()
    {
        return $this->belongsTo(CourseBlock::class, 'course_block_id', 'course_block_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    /**
     * Meeting days loaded by AttendanceSessionRepository so the service can
     * decide whether this schedule is allowed to start today.
     */
    public function scheduleDays()
    {
        return $this->hasMany(ScheduleDay::class, 'schedule_id', 'schedule_id');
    }
}
