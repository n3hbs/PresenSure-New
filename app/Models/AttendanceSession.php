<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AttendanceSession extends Model
{
    use HasFactory;

    // This table uses attendance_session_id instead of Laravel's default id.
    protected $primaryKey = 'attendance_session_id';

    // These are the fields that the repository may mass-assign during creation.
    protected $fillable = [
        'session_code',
        'schedule_id',
        'period_id',
        'instructor_id',
        'ble_device_id',
        'verification_mode',
        'ble_broadcast_token',
        'ble_token_expires_at',
        'requires_periodic_verification',
        'status',
        'start_at',
        'end_at',
        'device_started_at',
    ];

    // Never expose the stored token hash during normal model serialization.
    protected $hidden = [
        'ble_broadcast_token',
    ];

    // Convert database values into useful PHP date and Boolean values.
    protected $casts = [
        'ble_token_expires_at' => 'datetime',
        'requires_periodic_verification' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'device_started_at' => 'datetime',
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

    public function excuseRequests()
    {
        return $this->hasMany(ExcuseRequest::class, 'attendance_session_id', 'attendance_session_id');
    }
}
