<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'vote',
        'content',
        'email',
        'first_name',
        'last_name'
    ];

    public function profiles(){
        return $this->belongsTo(Profile::class);
    }
}
