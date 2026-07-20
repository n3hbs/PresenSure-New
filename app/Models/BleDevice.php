<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BleDevice extends Model
{
    use HasFactory;

    protected $primaryKey = 'ble_device_id';

    protected $fillable = [
        'public_device_id',
        'room_id',
        'device_secret',
        'is_active',
    ];

    protected $hidden = [
        'device_secret',
    ];

    protected $casts = [
        'device_secret' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}
