<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCourseBlock extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'user_course_block_id';

    protected $fillable = [
        'user_id',
        'course_block_id',
        'assigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function courseBlock()
    {
        return $this->belongsTo(CourseBlock::class, 'course_block_id', 'course_block_id');
    }
}
