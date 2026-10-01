<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'room_id';

    protected $fillable = [
        'building_id',
        'name',
        'floor_no',
        'capacity',
        'status',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id', 'building_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'room_id', 'room_id');
    }

    public function bleDevices()
    {
        return $this->hasMany(BleDevice::class, 'room_id', 'room_id');
    }
}
