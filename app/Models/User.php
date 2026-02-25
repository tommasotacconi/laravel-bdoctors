<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'home_address',
        'email',
        'password',
        'homonymous_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'home_address',
        'email_verified_at',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    protected $with = [
        'specializations'
    ];

    /**
     * Set homonymous_id last found homonym
     * if the only one
     *
     * @param array $data
     * @return User
     * @throws \Exception
     */
    public function setHomId(): User
    {
        // Verify presence of homonyms and assign homonymous_id
        $last_homonymous = User::where([
            ['first_name', $this->first_name],
            ['last_name', $this->last_name],
            ['id', '!=', $this->id]
        ])->orderByDesc('homonymous_id')->first();
        if ($last_homonymous !== null) {
            // Update even last homonymous if it had not homonyms yet
            if ($last_homonymous->homonymous_id === null) $last_homonymous->update(['homonymous_id' => 1]);
            $homonymous_id = $last_homonymous->homonymous_id + 1;
            $this->homonymous_id = $homonymous_id;
        }

        return $this;
    }

    public function specializations()
    {
        return $this->belongsToMany(Specialization::class);
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function specialization()
    {
        return $this->belongsTo(Specialization::class);
    }
}
