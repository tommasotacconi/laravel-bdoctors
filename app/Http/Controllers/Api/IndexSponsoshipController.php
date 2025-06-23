<?php

namespace App\Http\Controllers\Api;

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
        $sponsorshipsWithProfiles = Sponsorship::with([
            'profiles.user.specializations'
        ])->get();

        // Build profiles array, divided per sponsorship's type
        $profilesInSponsorships = array();
        foreach ($sponsorshipsWithProfiles as $sponsorshipWithProfiles) {
            $sponsorship_name = $sponsorshipWithProfiles->name;
            foreach ($sponsorshipWithProfiles->profiles as $profile) {
                // Add sponsorship name as a new attribute
                $profile->sponsorships[] = $sponsorship_name;
                // Consider if the profile is already present by another sponsorship
                $exist = false;
                $sameProfile = null;
                foreach ($profilesInSponsorships as $existingProfile) {
                    if ($existingProfile->id === $profile->id) {
                        $exist = true;
                        $sameProfile = $existingProfile;
                    }
                }
                // Add profile to array if it doesn't exist otherwise simply add the current
                // sponsorship
                if (!$exist) {
                    // Modify visible columns:
                    // -for profiles
                    $profile->makeHidden(['id', 'user_id', 'created_at', 'updated_at', 'pivot.profile_id', 'pivot']);
                    // -for users
                    $profile->user->makeHidden(['id', 'email', 'email_verified_at', 'home_address', 'created_at', 'updated_at']);
                    // -for specializations
                    if ($profile->user->relationLoaded('specializations') && $profile->user->specializations) {
                        $profile->user->specializations->makeHidden(['id', 'created_at', 'updated_at', 'pivot']);
                    }

                    $profilesInSponsorships[] = $profile;
                } else {
                    /** @var \App\Models\Profile $sameProfile */
                    $sameProfile->sponsorships[] = $sponsorship_name;
                }
            }
        };

        // Sort by user's last_name (surname)
        $sortedProfiles = collect($profilesInSponsorships)->sortBy(function ($profile) {
            return $profile->user->last_name ?? '';
        })->values();

        // Pagination
        $perPage = $request->input('per_page', 20);
        $currentPage = $request->input('page', 1);
        $currentItems = $sortedProfiles->slice(($currentPage - 1) * $perPage, $perPage)->values()->all();

        $paginator = new LengthAwarePaginator(
            $currentItems,
            $sortedProfiles->count(),
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
