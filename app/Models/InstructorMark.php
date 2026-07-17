<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorMark extends Model
{
    use HasFactory;

    protected $primaryKey = 'instructor_mark_id';

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'reason',
        'details',
        'status',
        'instructor_marked_at',
    ];

    protected $casts = [
        'instructor_marked_at' => 'datetime',
    ];

    public function attendanceRecord()
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_record_id', 'attendance_record_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
