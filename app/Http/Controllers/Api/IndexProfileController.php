<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;

class IndexProfileController extends Controller
{
    public function index()
    {
        $profiles = Profile::with(['user.specializations', 'sponsorships'])->get();
        //dd($profiles);
        return response()->json([
            'profiles' => $profiles
        ]);
    }
}
