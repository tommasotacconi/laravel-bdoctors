<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class EditProfileController extends Controller
{
    public function edit()
    {
        try {
            // Load profile with user and specializations in a single query
            $authUserId = Auth::id();

            $profile = Profile::with(['user.specializations'])
                ->where('user_id', $authUserId)->firstOrFail();

            // Transform the data into a cleaner format
            $photoName = str_contains($profile->photo, 'photos/') ? explode('photos/', $profile->photo)[1] : 'seeded photos';
            $curriculumName = str_contains($profile->curriculum, 'curricula/') ? explode('curricula/', $profile->curriculum)[1] : 'seeded cv text';
            $responseData = [
                'photo' => $photoName,
                'curriculum' => $curriculumName,
                ...$profile->makeHidden(['photo', 'curriculum', 'user'])->toArray(),
                'doctor' => [
                    ...$profile->user->makeHidden('specializations')->toArray(),
                    'specializations' => $profile->user->specializations->makeHidden(['created_at', 'updated_at'])
                ],
            ];

            Log::info('Profile retrieved successfully', ['profile_id' => $authUserId]);

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profile not found', ['profile_id' => $authUserId]);
            return response()->json([
                'success' => false,
                'message' => 'The requested profile could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile', [
                'profile_id' => $authUserId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the profile'
            ], 500);
        }
    }
}
