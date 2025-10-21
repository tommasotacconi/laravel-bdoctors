<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ValidatedInput;

class UpdateProfileController extends Controller
{
    public function update(Request $request)
    {
        try {
            $validated = $this->validateProfileData($request);
            $profile = Profile::with('user.specializations')->where('user_id', Auth::id())->first();

            $this->updateSingleModel($profile->user, $validated, $request);
            $this->updateSingleModel($profile, $validated, $request);

            $profile->load('user.specializations');
            Log::info('Profile updated successfully', ['profile_id' => $profile->id]);

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile_with_user' => $profile,
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
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateProfileData(Request $request): array
    {
        return $request->validate([
            'first_name' => ['string', 'max:50'],
            'last_name' => ['string', 'max:50'],
            'home_address' => ['string', 'max:100'],
            'specializations_id.*.id' => ['exists:specializations,id'],
            'office_address' => ['string', 'max:100'],
            'phone' => ['string', 'max:20'],
            'services' => ['string', 'min:5', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,url', 'max:2048'],
            'curriculum' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf,string', 'max:2048'],
        ]);
    }

    /**
     * Upate the passed model
     *
     * @param Model $model
     * @param array $validated
     * @param Request $req
     * @return void
     */
    private function updateSingleModel(Model $model, array $validated, Request $req)
    {
        $modelCols =  \Schema::getColumnListing($model->getTable());
        $modelCols = array_diff($modelCols, ['id', 'created_at', 'updated_at']);

        foreach ($validated as $key => $value) {
            if (!in_array($key, $modelCols) && $key !== 'specializations_id') continue;

            switch ($key) {
                case 'photo':
                    if ($req->hasFile('photo')) $this->handleFileUpload($value, $key, $key . 's',  $model);
                    break;

                case 'curriculum':
                    if ($req->hasFile('curriculum')) $this->handleFileUpload($value, $key, 'curricula', $model);
                    break;

                case 'specializations_id':
                    if (method_exists($model, 'specializations')) {
                        $updatedSpecIds = array_map(fn($el) => $el['id'], $value);
                        $model->specializations()->sync($updatedSpecIds);
                    }
                    break;

                default:
                    $model->$key = $value;
            }
        }


        $model->save();
    }

    private function handleFileUpload(UploadedFile $file, string $fileName, string $dbDirectory, Model $model): void
    {
        $name = $file->getClientOriginalName();
        $path = $file->storeAs($dbDirectory, $name, 'public');
        $model->$fileName = $path;
    }
}
