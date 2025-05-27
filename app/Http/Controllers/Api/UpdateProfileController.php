<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UpdateProfileController extends Controller
{
    public function update(Request $request, string $id)
    {
        try {
            $validated = $this->validateProfileData($request);

            $profile = Profile::findOrFail($id);

            $user = User::with('specializations')->findOrFail($id);
            //$newSpecializations = $request['specializations'];

            $specNuove = $validated['specializations'];
            $specVecchie = $user->specializations;

            //if (count($validated['specializations']) == 0) {
            $user->specializations()->sync($validated['specializations']);
            //} else {
            //$user->specializations()->sync($request['oldSpecializations']);
            //}


            //$user->load('specializations');

            // Updating the relation with users-specializations
            // $user = User::findOrFail($id);
            // $newSpecializations = $request['specializations'];
            // foreach($newSpecializations as $singleSpec) {
            //     $user->specializations()->attach($singleSpec['id']);
            //     $user->load('specializations');
            // }
            //$user->specializations()->attach($request['specializations[0]']);
            //$user->load('specializations');


            $profile->phone = $validated['phone'];
            $profile->office_address = $validated['office_address'];
            $profile->services = $validated['services'];

            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $profile->photo = $path;

                $photoUrl = asset('storage/' . $path);

                // return response()->json(['photoUrl' => $photoUrl]);
            }

            if ($request->hasFile('curriculum')) {
                $path = $request->file('curriculum')->store('curricula', 'public');
                $profile->curriculum = $path;

                $curriculumUrl = asset('storage/' . $path);

                // return response()->json(['curriculumUrl' => $curriculumUrl]);
            }

            $profile->save();

            // return back()->with('success', 'Profilo aggiornato con successo!');

            Log::info('Profile updated successfully', ['profile_id' => $profile->id]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => $profile,
                //'specializations' => $newSpecializations,
                'user' => $user,
                'specNuove' => $specNuove,
                'specVecchie' => $specVecchie
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Profile update validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                //'specializations' =>$newSpecializations
            ], 422);
        } catch (\Exception $e) {
            Log::error('Updating Profile failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Update Profile failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate profile data
     * daaaaaaaai
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateProfileData(Request $request)
    {
        return $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'curriculum' => ['nullable', 'mimes:jpeg,png,jpg,pdf,string', 'max:2048'],
            'photo' => ['nullable', 'mimes:jpeg,png,jpg,url', 'max:2048'],
            'office_address' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'services' => ['required', 'string', 'min:5', 'max:100'],
            'specializations' => ['exists:specializations,id'],
        ]);
    }
}
