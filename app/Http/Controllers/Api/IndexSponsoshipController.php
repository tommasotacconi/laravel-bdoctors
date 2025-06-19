<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use Illuminate\Http\Request;

class IndexSponsoshipController extends Controller
{
    public function index()
    {

        $sponsorships = Sponsorship::with(['profiles.user.specializations'])->get();
        return response()->json([
            'success' => true,
            'sponsorships' => $sponsorships
        ]);
    }
}
