<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Profiles\CreateProfile;
use App\Actions\Profiles\EditProfile;
use App\Actions\Profiles\UpdateProfile;
use App\Http\Responses\RespondsWithApi;
use App\Validation\BaseValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use RespondsWithApi;

    public function __construct(protected Request $req) {}

    /**
     * Handle create profile request
     *
     * @return JsonResponse
     */
    public function store(CreateProfile $creator)
    {
        $validated = $this->req->validate(BaseValidation::profileToCreate());

        return $this->apiResponse(
            $creator->handle($this->req->user(), $validated),
            'profile',
            'profile created'
        );
    }

    public function edit(EditProfile $finder)
    {
        return $this->apiResponse(
            $finder->handle($this->req->user()),
            'profile',
            'profile retrieved for edit'
        );
    }

    public function update(UpdateProfile $updater)
    {
        $validated = [
            'profile' => $this->req->validate(BaseValidation::profile()),
            'user' => $this->req->validate(BaseValidation::user())
        ];
        $profile = $updater->handle($this->req->user(), $validated['profile'], $validated['user']);


        return $this->apiResponse(
            $profile,
            'profile',
            'profile updated'
        );
    }
}
