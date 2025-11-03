<?php

namespace App\Models;

use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'curriculum',
        'photo',
        'office_address',
        'phone',
        'services'
    ];

    protected $hidden = [
        'id',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function sponsorships()
    {
        return $this->belongsToMany(Sponsorship::class)->withPivot(['start_date', 'end_date']);
    }

    public function activeSponsorship(): BelongsToMany
    {
        $computedTime = TimeHelper::computeAppTime(false);
        return $this->sponsorships()
            ->wherePivot('start_date', '<', $computedTime)
            ->wherePivot('end_date', '>', $computedTime);
    }
}
