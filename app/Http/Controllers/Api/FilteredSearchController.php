<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Specialization;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FilteredSearchController extends Controller
{
    public function filter(string $specialization, ?string $rating = null, ?string $reviews = null)
    {
        if ($specialization) {
            // `$specialization` manipulation to decode its name from the URL
            $specialization = str_replace(array('-', '_'), array(' ', '-'), $specialization);
            $specialization = preg_replace_callback(
                '/^./',
                function ($matches) {
                    return strtoupper($matches[0]);
                },
                $specialization
            );

            try {
                // Check specialization existance before executing complete query
                $query = Specialization::select('name')->where('name', '=', $specialization);
                if ($query->get()->isEmpty()) {
                    throw new Exception('Specialization not found');
                }
                Log::info('Filtering reviews for specialization: ' . $query->get());
            } catch (Exception $e) {
                //throw $th;
                return response()->json(['Error' => ['message' => $e->getMessage()]], 404);
            }

            $query = Profile::select('profiles.*', 'specializations.id as specializations_id', 'specializations.name as specialization_name')
                ->join('users', 'profiles.user_id', '=', 'users.id')
                ->join('specialization_user', 'users.id', '=', 'specialization_user.user_id')
                ->join('specializations', 'specialization_user.specialization_id', '=', 'specializations.id')
                // sponsorship
                // ->join('profile_sponsorship', 'profiles.id', '=', 'profile_sponsorship.profile_id')
                // ->join('sponsorships', 'sponsorships.id', '=', 'profile_sponsorship.sponsorship_id')
                ->leftJoin('reviews', 'profiles.id', '=', 'reviews.profile_id')
                ->where('specializations.name', '=', $specialization)
                ->groupBy('profiles.id', 'users.id', 'specializations.id')->with(['user', 'user.specializations']);

            $query->selectRaw(
                'ROUND(AVG(reviews.vote), 0) AS avg_vote, COALESCE(COUNT(reviews.id), 0) AS total_reviews'
            );

            if ($rating !== null && $rating !== "null") {
                $query->havingRaw('avg_vote >= ?', [$rating]);
            }

            if ($reviews !== null && $reviews !== "null") {
                $query->havingRaw('total_reviews >= ?', [$reviews]);
            }
        }
        $users = $query->get();
        $users->makeHidden(['id', 'user_id']);
        Log::info('Finished filtering process');

        return response()->json($users);
    }
}
