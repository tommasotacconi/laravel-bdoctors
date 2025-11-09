<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'content',
        'email',
        'first_name',
        'last_name',
    ];

    protected $hidden = [
        'pivot',
    ];

    public function profiles()
    {
        return $this->belongsTo(Profile::class);
    }
}
