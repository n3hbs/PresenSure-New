<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BleDetection extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'ble_detections';

    protected $primaryKey = 'ble_detection_id';

    protected $fillable = [
        'attendance_record_id',
        'user_id',
        'rssi',
        'detected_at',
    ];

    protected $casts = [
        'rssi' => 'integer',
        'detected_at' => 'datetime',
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
