<?php

namespace App\Actions\Profiles;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class EditProfile
{
    public function __construct(protected Request $req) {}

    public function handle(User $user): Profile
    {
        $profile = $user->loadMissing('profile')->profile;
        // Manage seeded field
        $profile->setRelation('user', $user->makeHidden('profile'));
        $profile->user->makeVisible('home_address');

        return $profile;
    }
}
