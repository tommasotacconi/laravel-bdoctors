<?php

namespace App\Models;

use App\Helpers\TimeHelper;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'activeSponsorshipPivot'
    ];

    protected function activeSponsorship(): Attribute
    {
        return Attribute::get(fn () => $this->activeSponsorshipPivot?->sponsorship->name);
    }

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
        return $this->belongsToMany(Sponsorship::class)
            ->using(ProfileSponsorship::class)
            ->withPivot(['start_date', 'end_date']);
    }

    public function activeSponsorshipPivot() {
        return $this->hasOne(ProfileSponsorship::class)->active();
    }
}
