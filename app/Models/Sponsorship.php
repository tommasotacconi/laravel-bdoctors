<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    use HasFactory;

    protected $hidden = [
        'created_at',
        'updated_at',
        'pivot'
    ];

    public function profiles()
    {
        return $this->belongsToMany(Profile::class)->withPivot(['start_date', 'end_date']);
    }
}
