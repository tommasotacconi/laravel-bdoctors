<?php

namespace App\Actions\Profile;

use App\Models\Profile;
use App\Models\User;

class GetProfile
{
    public function handle(?int $id = null, ?User $user = null)
    {
        try {
            $profile = Profile::with([
                'user.specializations',
                'activeSponsorshipPivot.sponsorship',
                /* 'reviews' */
            ])->where('id', $id)->orWhere('user_id', $user->id)->firstOrFail()->append('active_sponsorship');
            $profile->user->makeVisible('home_address');

            return $profile;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return null;
        }
    }
}
