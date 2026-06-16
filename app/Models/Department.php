<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;
    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_code',
        'department_name'
    ];

    public function program()
    {
        return $this->hasMany(Program::class, 'department_id', 'department_id');
    }

    public function instructor()
    {
        return $this->hasMany(Instructor::class, 'department_id', 'department_id');
    }
}

