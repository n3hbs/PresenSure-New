<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserRole extends Model
{
    use HasFactory;
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'role_id',
        'assigned_at'
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
