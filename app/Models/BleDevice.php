<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BleDevice extends Model
{
    use HasFactory;

    // This table uses ble_device_id instead of Laravel's default id.
    protected $primaryKey = 'ble_device_id';

    // The repository/service can populate these registered-device fields.
    protected $fillable = [
        'public_device_id',
        'device_name',
        'room_id',
        'device_secret',
        'status',
    ];

    // The device secret is authentication material and must not reach JSON.
    protected $hidden = [
        'device_secret',
    ];

    // Laravel encrypts the secret before storage and decrypts it when accessed.
    protected $casts = [
        'device_secret' => 'encrypted',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }

    public function isAvailable(): bool
    {
        // Only devices in the active operational state may start a session.
        return $this->status === 'active';
    }
}
