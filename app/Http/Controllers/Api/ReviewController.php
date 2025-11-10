<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Specialization;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function App\Helpers\makeResponseWithCreated;

class ReviewController extends Controller
{
    public function index()
    {

        $authenticatedUserProfileId = Profile::where('user_id', Auth::id())->firstOrFail()->id;
        $reviews = Review::where('profile_id', $authenticatedUserProfileId)->orderByDesc('created_at')->get();
        //dd($reviews);
        return response()->json([
            'success' => true,
            'reviews' => $reviews
        ]);
    }

    public function create(Request $request)
    {
        return makeResponseWithCreated('Review', function () use ($request) {
            $validated = $this->validateReviewData($request);
            $user = User::where($validated['doctor_details'])->with('profile')->firstOrFail();

            return Review::create([
                ...$validated,
                'profile_id' => $user->profile->id
            ]);
        });
    }

    public function filter(string $specialization, ?string $rating = null, ?string $reviews = null)
    {
        // `$specialization` manipulation to decode its name from the URL
        $specialization = str_replace(array('-', '_'), array(' ', '-'), $specialization);
        $specialization = preg_replace_callback('/^./', fn ($matches) => strtoupper($matches[0]), $specialization);

        // Check specialization existance before executing complete query
        try {
            $spec = Specialization::select('name')->where('name', '=', $specialization);

            if ($spec->get()->isEmpty()) throw new Exception('Specialization not found');
        } catch (Exception $e) {
            //throw $th;
            return response()->json(['Error' => ['message' => $e->getMessage()]], 404);
        }

        $query = Profile::select('profiles.*', 'specializations.name as specialization_name')
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->join('specialization_user', 'users.id', '=', 'specialization_user.user_id')
            ->join('specializations', 'specialization_user.specialization_id', '=', 'specializations.id')
            ->leftJoin('reviews', 'profiles.id', '=', 'reviews.profile_id')
            ->where('specializations.name', '=', $specialization)
            ->groupBy('profiles.id', 'specializations.id')->with(['user.specializations', 'reviews', 'activeSponsorship']);
        if ($rating !== null && $rating !== "null")
            $query->selectRaw('ROUND(AVG(reviews.vote), 0) AS avg_vote')->havingRaw('avg_vote >= ?', [$rating]);
        if ($reviews !== null && $reviews !== "null")
            $query->selectRaw('COALESCE(COUNT(reviews.id), 0) AS total_reviews')->havingRaw('total_reviews >= ?', [$reviews]);
        $users = $query->get();

        return response()->json($users);
    }

}
