<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\RespondsWithApi;
use App\Models\Sponsorship;

class SponsorshipController extends Controller
{
    use RespondsWithApi;

    public function index()
    {
        return $this->apiResponse(
            Sponsorship::all(),
            'sponsorships',
        );
    }
}
