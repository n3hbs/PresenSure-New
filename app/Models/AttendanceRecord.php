<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_record_id';

    protected $fillable = [
        'attendance_session_id',
        'student_id',
        'ble_verified',
        'face_verified',
        'presence_verified',
        'ble_verified_at',
        'face_verified_at',
        'verified_at',
        'status',
    ];

    protected $casts = [
        'ble_verified' => 'boolean',
        'face_verified' => 'boolean',
        'presence_verified' => 'boolean',
        'ble_verified_at' => 'datetime',
        'face_verified_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function attendanceSession()
    {
        return $this->belongsTo(AttendanceSession::class, 'attendance_session_id', 'attendance_session_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id', 'user_id');
    }

    public function bleDetections()
    {
        return $this->hasMany(BleDetection::class, 'attendance_record_id', 'attendance_record_id');
    }

    public function instructorMarks()
    {
        return $this->hasMany(InstructorMark::class, 'attendance_record_id', 'attendance_record_id');
    }
}
