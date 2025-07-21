<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateReviewController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = $this->validateReviewData($request);

            $user = User::where([
                ['homonymous_id', $validated['doctor_details']['homonymous_id']],
                ['first_name', $validated['doctor_details']['first_name']],
                ['last_name', $validated['doctor_details']['last_name']],
            ])->with('profile')->firstOrFail();

            $newReview = new Review();
            $newReview->profile_id = $user->profile->id;
            $newReview->content = $validated['content'];
            $newReview->email = $validated['email'];
            $newReview->first_name = $validated['first_name'];
            $newReview->last_name = $validated['last_name'];
            $newReview->votes = $validated['votes'];
            $newReview->save();

            Log::info('Review created successfully', ['review_id' => $newReview->id]);

            return response()->json([
                'message' => 'Review created successfully',
                'profile' => $newReview
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Review creation validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Review creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Review creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
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
