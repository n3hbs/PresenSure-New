<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Program extends Model
{
    use HasFactory;
    protected $primaryKey = 'program_id';
    protected $fillable = [
        'department_id',
        'program_code',
        'program_name',
        'program_years'
    ];

    public function student()
    {
        return $this->hasMany(Student::class, 'program_id', 'program_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
}
