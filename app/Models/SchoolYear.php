<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchoolYear extends Model
{
    use HasFactory;
    protected $primaryKey = 'school_year_id';
    protected $fillable = [
        'school_year_start',
        'school_year_end'
    ];

    public function semester(){
        return $this->hasMany(Semester::class, 'school_year_id', 'school_year_id');
    }
}
