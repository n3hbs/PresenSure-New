<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'course_id';

    protected $fillable = [
        'subject_code',
        'name',
    ];

    public function courseBlocks()
    {
        return $this->hasMany(CourseBlock::class, 'course_id', 'course_id');
    }
}
