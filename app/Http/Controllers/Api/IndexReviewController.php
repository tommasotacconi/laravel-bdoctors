<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class IndexReviewController extends Controller
{
    public function index()
    {

        $authenticatedUserId = Auth::id();
        $authenticatedUserProfileId = Profile::where('user_id', $authenticatedUserId)->firstOrFail()->id;
        $reviews = Review::where('profile_id', $authenticatedUserProfileId)->orderByDesc('created_at')->get();
        //dd($reviews);
        return response()->json([
            'success' => true,
            'reviews' => $reviews
        ]);
    }
}
