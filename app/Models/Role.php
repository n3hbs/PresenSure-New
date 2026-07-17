<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model {
    use HasFactory;
    protected $primaryKey = 'role_id';
    protected $fillable = [
        'role_name',
        'description'
    ];

    public function userRole(){
        return $this->hasMany(UserRole::class, 'role_id', 'role_id');
    }

    public function rolePermissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
            ->withPivot('assigned_at');
    }
}
