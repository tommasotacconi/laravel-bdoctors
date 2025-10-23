<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TimeHelper;
use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

class ShowProfileController extends Controller
{
    /**
     * Display the specified profile with user data.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $name)
    {
        $userId = '';
        if ($name === 'authenticated') {
            $authenticatedUserId = Auth::id();

            if (!$authenticatedUserId)
                return response()->json(['message' => 'Unauthorized request'], 401);

            $userId = $authenticatedUserId;
            Log::info('Authenticated user of id ' . $authenticatedUserId . ' is accessing');
        } else {
            $nameElements = explode('-', $name);
            $firstName = $nameElements[0];
            $lastName = $nameElements[1];
            $homonymousId = null;

            if (count($nameElements) === 3)
                $homonymousId = $nameElements[2];

            $requestedUser = User::where([
                ['first_name', $firstName],
                ['last_name', $lastName],
                ['homonymous_id', $homonymousId]
            ])->firstOrFail();
            $userId = $requestedUser->id;
            Log::info('Requested user of id ' . $requestedUser->id . ' is being shown');
        }

        try {
            // Load profile with user and specializations in a single query
            $profile = Profile::with(['user.specializations', /* 'reviews' */])
                ->where('user_id', $userId)->firstOrFail();
            $activeSpons = $profile->activeSponsorship()?->makeHidden('profile_id');

            // Transform the data into a cleaner format
            $responseData = [
                ...$profile->toArray(),
                'user' => [
                    ...$profile->user->toArray(),
                    'specializations' => $profile->user->specializations->makeHidden(['created_at', 'updated_at'])
                ],
                'active_sponsorship' => [
                    ...($activeSpons ? $activeSpons : []),
                ]
            ];

            Log::info('Profile retrieved successfully', ['profile_id' => $userId]);

            return response()->json([
                'success' => true,
                'profile' => $responseData
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Profile not found', ['name_param' => $name]);
            $user = User::findOrFail($userId);

            return response()->json([
                'success' => false,
                'user' => [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name
                ],
                'message' => 'The requested profile could not be found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve profile', [
                'name_param' => $name,
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
