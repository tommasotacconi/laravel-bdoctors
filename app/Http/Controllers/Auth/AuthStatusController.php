<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthStatusController extends Controller
{
    public function __invoke(Request $request)
    {
        return response()->json([
            'authenticated' => auth()->check(),
            'user' => auth()->user()
        ]);
    }
}
