<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Building extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'building_id';

    protected $fillable = [
        'code',
        'name',
    ];

    public function rooms()
    {
        return $this->hasMany(Room::class, 'building_id', 'building_id');
    }
}
