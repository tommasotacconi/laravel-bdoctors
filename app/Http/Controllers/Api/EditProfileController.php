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

            $profile = Profile::with(['user.specializations', 'messages', 'sponsorships'])
                ->where('user_id', $authUserId)->firstOrFail();

            // Transform the data into a cleaner format
            $responseData = [
                'id' => $profile->id,
                'curriculum' => $profile->curriculum,
                'photo' => $profile->photo,
                'office_address' => $profile->office_address,
                'phone' => $profile->phone,
                'services' => $profile->services,
                'doctor' => [
                    'id' => $profile->user->id,
                    'first_name' => $profile->user->first_name,
                    'last_name' => $profile->user->last_name,
                    'email' => $profile->user->email,
                    'specializations' => $profile->user->specializations->map(function ($spec) {
                        return [
                            'id' => $spec->id,
                            'name' => $spec->name
                        ];
                    })
                ],
                'has_active_sponsorship' => $profile->sponsorships->where('pivot.end_date', '>', now())->isNotEmpty()
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
