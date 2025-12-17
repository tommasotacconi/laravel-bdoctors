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

class SponsorshipController extends Controller
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
        $sponsoredProfiles = Profile::has('activeSponsorshipPivot')
            ->with(['user.specializations', 'activeSponsorshipPivot.sponsorship'])->get();
        $sortedSponsoredProfiles = $sponsoredProfiles->sortByDesc(function (object $sponsored, int $key) {
            return $sponsored->activeSponsorshipPivot->start_date;
        })->values();

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
