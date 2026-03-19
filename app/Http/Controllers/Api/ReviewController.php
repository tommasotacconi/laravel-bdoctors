<?php

namespace App\Http\Controllers\Api;

use App\Actions\Review\FilterReview;
use App\Http\Controllers\Controller;
use App\Http\Responses\RespondsWithApi;
use App\Models\Profile;
use App\Models\Review;
use App\Models\User;
use App\Validation\BaseValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    use RespondsWithApi;

    public function __construct(protected Request $req, protected BaseValidation $bV) {}

    public function index()
    {
        $authenticatedUserProfileId = Profile::where('user_id', Auth::id())->firstOrFail()->id;
        $reviews = Review::where('profile_id', $authenticatedUserProfileId)->orderByDesc('created_at')->get();

        return $this->apiResponse(
            $reviews,
            'reviews'
        );
    }

    public function create()
    {
        $validated = $this->req->validate($this->bV::review());
        $user = User::where($validated['doctor_details'])->with('profile')->firstOrFail();

        return $this->apiResponse(
            Review::create([
                ...$validated,
                'profile_id' => $user->profile->id
            ]),
            'review',
            'review sent'
        );
    }

    public function filter(FilterReview $filter, string $specialization, ?string $rating = null, ?string $reviews = null)
    {
        // '$specialization' manipulation to decode its name from the URL
        $specialization = str_replace(array('-', '_'), array(' ', '-'), $specialization);
        $specialization = preg_replace_callback('/^./', fn($matches) => strtoupper($matches[0]), $specialization);
        if (!is_null($rating)) $rating = (int) $rating;
        if (!is_null($reviews)) $reviews = (int) $reviews;

        return $this->apiResponse(
            $filter->handle($specialization, $rating, $reviews),
            'profiles'
        );
    }
}
