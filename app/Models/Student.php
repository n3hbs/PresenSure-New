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
        'semester_id',
        'program_id',
        'year',
        'block',
        'status',
    ];

    public function user() 
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function program()
    {
        return $this->belongsTo(Program::class, 'program_id', 'program_id');
    }

    public function semester(){
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
