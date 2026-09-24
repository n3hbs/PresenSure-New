<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = null;

    protected $fillable = [
        'user_id',
        'permission_id',
        'is_granted',
        'assigned_at',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }
}
