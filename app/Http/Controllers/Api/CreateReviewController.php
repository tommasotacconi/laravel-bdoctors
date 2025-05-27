<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateReviewController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = $this->validateReviewData($request);

            $newReview = new Review();

            $newReview->profile_id = $validated['profile_id'];
            $newReview->content = $validated['content'];
            $newReview->email = $validated['email'];
            $newReview->first_name = $validated['first_name'];
            $newReview->last_name = $validated['last_name'];
            $newReview->votes = $validated['votes'];




            $newReview->save();

            Log::info('Profile created successfully', ['review_id' => $newReview->id]);

            return response()->json([
                'message' => 'Message created successfully',
                'profile' => $newReview
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Profile creation validation failed', ['errors' => $e->errors()]);
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
        return $request->validate([
            'profile_id' => ['required', 'exists:profiles,id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|it|org|net|edu|gov)$/'
            ],
            'content' => ['required', 'string', 'min:5', 'max:300'],
            'votes' => ['integer', 'min:0', 'max:5']
        ]);
    }
}
