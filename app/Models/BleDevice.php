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
        'device_name',
        'room_id',
        'device_secret',
        'status',
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

    public function isAvailable(): bool
    {
        return $this->is_active && $this->status === 'active';
    }
}
