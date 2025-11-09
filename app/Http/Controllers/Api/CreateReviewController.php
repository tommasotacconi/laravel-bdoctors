<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function App\Helpers\makeResponseWithCreated;

class CreateReviewController extends Controller
{
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

    /**
     * Validate profile data
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateReviewData(Request $request)
    {
        return $request->validate(ValidationRules::review());
    }
}
