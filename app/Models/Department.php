<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'department_id';

    protected $fillable = [
        'department_code',
        'department_name',
        'description',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class, 'department_id', 'department_id');
    }

    public function program(): HasMany
    {
        return $this->programs();
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(Instructor::class, 'department_id', 'department_id');
    }

    public function instructor(): HasMany
    {
        return $this->instructors();
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            Program::class,
            'department_id',
            'program_id',
            'department_id',
            'program_id'
        );
    }
}
