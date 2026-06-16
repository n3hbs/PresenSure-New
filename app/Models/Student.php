<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;
    protected $primaryKey = 'student_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'student_id',
        'user_id',
        'program_id',
        'year',
        'block',
        'status',
    ];

    public function user() 
    {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    public function program()
    {
        return $this->hasOne(Program::class, 'program_id', 'program_id');
    }
}
