<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateResourceResponse;
use App\Actions\Profiles\CreateProfile;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CreateProfileController extends Controller
{
    /**
     * Handle create profile request
     *
     * @param Request $request
     * @return \Illuminate\
     */
    public function create()
    {
        $res = new CreateResourceResponse();

        return $res->handle('profile', 'creation', 'created', [app(CreateProfile::class), 'handle']);
    }
}
