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
    public function filter($specialization, $rating, $reviews)
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
                return response()->json(['Error' => ['message' => $e->getMessage()]]);
            }

            $query = User::select('users.*', 'profiles.*', 'specializations.id as specializations_id', 'specializations.name as specializations_name',)
                ->where('specializations.name', '=', $specialization)
                ->join('specialization_user', 'users.id', '=', 'specialization_user.user_id')
                ->join('specializations', 'specializations.id', '=', 'specialization_user.specialization_id')
                ->join('profiles', 'users.id', '=', 'profiles.user_id')
                // sponsorship
                // ->join('profile_sponsorship', 'profiles.id', '=', 'profile_sponsorship.profile_id')
                // ->join('sponsorships', 'sponsorships.id', '=', 'profile_sponsorship.sponsorship_id')
                ->leftJoin('reviews', 'profiles.id', '=', 'reviews.profile_id')
                ->groupBy('users.id', 'profiles.id', 'specializations.id');

            $query->selectRaw(
                'CEIL(AVG(reviews.votes)) AS media_voti, COALESCE(COUNT(reviews.id), 0) AS totalReviews'
            );

            if ($rating !== null) {
                $query->havingRaw('COALESCE(AVG(reviews.votes), 0) >= ?', [$rating]);
            }

            if ($reviews !== null) {
                $query->havingRaw('COALESCE(COUNT(reviews.id), 0) >= ?', [$reviews]);
            }
        }
        $users = $query->get();
        Log::info('Finished filtering process');

        return response()->json($users);
    }
}
