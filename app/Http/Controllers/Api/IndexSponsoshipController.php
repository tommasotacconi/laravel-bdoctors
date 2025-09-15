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
