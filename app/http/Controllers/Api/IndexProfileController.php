<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;

class IndexProfileController extends Controller
{
    public function index()
    {
        $profiles = Profile::with(['user.specializations', 'messages', 'reviews', 'sponsorships'])->get();
        //dd($profiles);
        return response()->json([
            'profiles' => $profiles
        ]);
    }
}
