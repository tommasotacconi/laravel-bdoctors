<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class IndexProfileController extends Controller
{
    public function index(?string $nameId = null)
    {
        if ($nameId) {
            $nameIdElements = explode('-', $nameId);
            $firstName = $nameIdElements[0];
            $lastName = $nameIdElements[1];
            $homonymousId = null;
            if (count($nameIdElements) === 3)
                $homonymousId = $nameIdElements[2];
            $requestedUser = User::where([
                ['first_name', $firstName],
                ['last_name', $lastName],
                ['homonymous_id', $homonymousId]
            ])->firstOrFail();
            $requestedUserProfile = Profile::where('user_id', $requestedUser->id)->firstOrFail();

            return response()->json([
                'profile' => [
                    ...$requestedUserProfile->toArray(),
                    'user' => $requestedUser
                ]
            ]);
        }

        $profiles = Profile::with(['user.specializations', 'sponsorships'])->get();
        //dd($profiles);
        return response()->json([
            'profiles' => $profiles
        ]);
    }
}
