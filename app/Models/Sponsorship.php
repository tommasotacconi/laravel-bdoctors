<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sponsorship extends Model
{
    use HasFactory;

    public function profiles()
    {
        return $this->belongsToMany(Profile::class)->withPivot(['start_date', 'end_date']);
    }
}
