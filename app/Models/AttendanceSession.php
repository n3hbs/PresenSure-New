<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_session_id';

    protected $fillable = [
        'schedule_id',
        'period_id',
        'instructor_id',
        'verification_mode',
        'ble_source_type',
        'beacon_id',
        'broadcaster_user_id',
        'ble_broadcast_token',
        'ble_token_expires_at',
        'status',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'ble_token_expires_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id', 'schedule_id');
    }

    public function period()
    {
        return $this->belongsTo(Period::class, 'period_id', 'period_id');
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id', 'user_id');
    }

    public function broadcaster()
    {
        return $this->belongsTo(User::class, 'broadcaster_user_id', 'user_id');
    }

    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'attendance_session_id', 'attendance_session_id');
    }
}
