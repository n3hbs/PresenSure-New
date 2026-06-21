<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;
    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'string';
    protected $fillable = [
        'user_id',
        'imagelink'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
