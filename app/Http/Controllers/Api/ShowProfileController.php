<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TimeHelper;
use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShowProfileController extends Controller
{
    /**
     * Display the specified profile with user data.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        $authenticatedUserId = Auth::id();
        if (!$authenticatedUserId)
            return response()->json(['message' => 'Unauthorized rquest'], 401);
        Log::info('Authenticated user of id ' . $authenticatedUserId . ' is accessing');

        try {
            // Load profile with user and specializations in a single query
            $profile = Profile::with(['user.specializations', 'messages', 'sponsorships'])
                ->where('user_id', $authenticatedUserId)->firstOrFail();
            $sponsorshipsRelation = $profile->sponsorships();
            $computedTime = TimeHelper::computeAppTime(false);

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
                'active_sponsorships' => $sponsorshipsRelation
                    ->wherePivot('start_date', '<', $computedTime)
                    ->wherePivot('end_date', '>', $computedTime)
                    ->orderByDesc('start_date')
                    ->get(),
            ];

            Log::info('Profile retrieved successfully', ['profile_id' => $authenticatedUserId]);

            return response()->json([
                'success' => true,
                'data' => $responseData
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profile not found', ['profile_id' => $authenticatedUserId]);
            return response()->json([
                'success' => false,
                'message' => 'The requested profile could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile', [
                'profile_id' => $authenticatedUserId,
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
