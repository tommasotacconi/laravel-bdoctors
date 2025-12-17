<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;

class SponsorshipController extends Controller
{
    public function index()
    {
        $sponsorships = Sponsorship::all();

        return response()->json([
            'success' => true,
            'sponsorships' => $sponsorships
        ]);
    }
}
