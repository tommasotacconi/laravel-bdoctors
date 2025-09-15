<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TimeHelper;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Sponsorship;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class IndexSponsoshipController extends Controller
{
    public function index()
    {
        $sponsorships = Sponsorship::all();

        return response()->json([
            'success' => true,
            'sponsorships' => $sponsorships
        ]);
    }

    public function sponsored(Request $request)
    {
        $sponsoredProfiles = Profile::has('activeSponsorship')
            ->with(['user.specializations', 'activeSponsorship'])->get();
        $sortedSponsoredProfiles = $sponsoredProfiles->sortByDesc(function (object $sponsored, int $key) {
            return $sponsored->activeSponsorship[0]['pivot']['start_date'];
        })->values();

        // Modify visible columns in profiles, users and specializations:
        $sponsoredProfiles->makeHidden([
            'id',
            'user_id',
            'created_at',
            'updated_at',
            'user.id',
            'user.email',
            'user.email_verified_at',
            'user.home_address',
            'user.created_at',
            'user.updated_at',
            'user.specializations.id',
            'user.specializations.created_at',
            'user.specializations.updated_at',
            'user.specializations.pivot',
            'active_sponsorship.id',
            'active_sponsorship.created_at',
            'active_sponsorship.updated_at',
            'active_sponsorship.pivot.profile_id',
            'active_sponsorship.pivot.sponsorship_id',
        ]);
        // $profile->user->makeHidden(['id', 'email', 'email_verified_at', 'home_address', 'created_at', 'updated_at']);
        // -for specializations
        // if ($profile->user->relationLoaded('specializations') && $profile->user->specializations) {
        //     $profile->user->specializations->makeHidden(['id', 'created_at', 'updated_at', 'pivot']);
        // }
        // // Build profiles array, divided per sponsorship's type
        // $profilesInSponsorships = array();
        // foreach ($sponsorshipsWithProfiles as $sponsorshipWithProfiles) {
        //     $sponsorship_name = $sponsorshipWithProfiles->name;
        //     foreach ($sponsorshipWithProfiles->profiles as $profile) {
        //         // Add sponsorship name as a new attribute
        //         $profile->allSponsorships = [];
        //         $profile->allSponsorships = array_merge($profile->allSponsorships, [$sponsorship_name]);
        //         // Consider if the profile is already present by another sponsorship
        //         $exist = false;
        //         $sameProfile = null;
        //         foreach ($profilesInSponsorships as $existingProfile) {
        //             if ($existingProfile->id === $profile->id) {
        //                 $exist = true;
        //                 $sameProfile = $existingProfile;
        //             }
        //         }
        //         // Add profile to array if it doesn't exist otherwise simply add the current
        //         // sponsorship
        //         if (!$exist) {
        //             // Modify visible columns:
        //             // -for profiles
        //             $profile->makeHidden(['id', 'user_id', 'created_at', 'updated_at', 'pivot.profile_id', 'pivot']);
        //             // -for users
        //             $profile->user->makeHidden(['id', 'email', 'email_verified_at', 'home_address', 'created_at', 'updated_at']);
        //             // -for specializations
        //             if ($profile->user->relationLoaded('specializations') && $profile->user->specializations) {
        //                 $profile->user->specializations->makeHidden(['id', 'created_at', 'updated_at', 'pivot']);
        //             }

        //             $profilesInSponsorships[] = $profile;
        //         } else {
        //             /** @var \App\Models\Profile $sameProfile */
        //             $sameProfile->allSponsorships[] = $sponsorship_name;
        //         }
        //     }
        // };

        // // Sort by user's last_name (surname)
        // $sortedProfiles = collect($profilesInSponsorships)->sortByDesc(function ($profile) {
        //     return $profile->pivot->start_date;
        // })->values();

        // Pagination
        $perPage = $request->input('per_page', 10);
        $currentPage = $request->input('page', 1);
        $currentItems = $sortedSponsoredProfiles->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();

        $paginator = new LengthAwarePaginator(
            $currentItems,
            $sortedSponsoredProfiles->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'success' => true,
            'paginated_profiles' => $paginator,
        ]);
    }
}
