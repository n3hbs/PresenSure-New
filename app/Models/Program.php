<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $primaryKey = 'program_id';

    protected $fillable = [
        'department_id',
        'program_code',
        'program_name',
        'program_years',
    ];

    public function students()
    {
        return $this->hasMany(Student::class, 'program_id', 'program_id');
    }

    public function student()
    {
        return $this->students();
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }
}
