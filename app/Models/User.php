<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'first_name',
        'middle_initial',
        'last_name',
        'suffix',
        'sex',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    public function userProfile() 
    {
        return $this->hasOne(UserProfile::class, 'user_id', 'user_id');
    }

    public function student()
    {   
        return $this->hasMany(Student::class, 'user_id', 'user_id');
    }
    
    public function roleAssignment(){
        return $this->hasOne(UserRole::class, 'user_id', 'user_id');
    }

    public function userCourseBlocks()
    {
        return $this->hasMany(UserCourseBlock::class, 'user_id', 'user_id');
    }

    public function bleDetections()
    {
        return $this->hasMany(BleDetection::class, 'user_id', 'user_id');
    }

    public function attendancePolicies()
    {
        return $this->hasMany(AttendancePolicy::class, 'user_id', 'user_id');
    }

    public function excuseRequests()
    {
        return $this->hasMany(ExcuseRequest::class, 'user_id', 'user_id');
    }

    public function instructorMarks()
    {
        return $this->hasMany(InstructorMark::class, 'user_id', 'user_id');
    }
}
