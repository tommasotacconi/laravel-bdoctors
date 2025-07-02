<?php

namespace App\Http\Controllers\Api;

use App\Models\Profile;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CreateProfileController extends Controller
{
    /**
     * Create a new profile for a user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        try {
            $validated = $this->validateProfileData($request);

            $profile = new Profile();

            $profile->user_id = Auth::id();
            $profile->phone = $validated['phone'];
            $profile->office_address = $validated['office_address'];
            $profile->services = $validated['services'];

            if ($request->hasFile('photo')) {
                $photo = $validated['photo'];
                $name = $photo->getClientOriginalName();
                $path = $photo->storeAs('photos', $name, 'public');
                $profile->photo = $path;

                $photoUrl = asset('storage/' . $path);

                // return response()->json(['photoUrl' => $photoUrl]);
            }

            if ($request->hasFile('curriculum')) {
                $curriculum = $validated['curriculum'];
                $name = $curriculum->getClientOriginalName();
                $path = $curriculum->storeAs('curricula', $name, 'public');
                $profile->curriculum = $path;

                $curriculumUrl = asset('storage/' . $path);

                // return response()->json(['curriculumUrl' => $curriculumUrl]);
            }

            $profile->save();

            Log::info('Profile created successfully', ['profile_id' => $profile->id]);

            return response()->json([
                'message' => 'Profile created successfully',
                'profile' => $profile
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Profile creation validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Profile creation failed',
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
    private function validateProfileData(Request $request)
    {
        return $request->validate([
            'curriculum' => ['required', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:2048'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'office_address' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'services' => ['required', 'string', 'min:5', 'max:100'],
        ]);
    }

    /**
     * Create a new profile
     *
     * @param array $data
     * @return Profile
     * @throws \Exception
     */
    private function createProfile(array $data)
    {
        Log::info('Attempting to create profile', $data);

        $profile = Profile::create($data);

        if (!$profile) {
            throw new \Exception('Failed to create profile');
        }

        return $profile;
    }
}
