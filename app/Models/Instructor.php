<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Instructor extends Model
{
    use HasFactory;
    protected $primaryKey = 'instructor_id';
    protected $fillable = [
        'user_id',
        'department_id',
        'status'
    ];

    public function department()
    {
        return $this->hasOne(Department::class, 'department_id', 'department_id');
    }
}
