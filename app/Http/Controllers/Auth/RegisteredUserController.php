<?php
namespace App\Http\Controllers\Auth;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class RegisteredUserController extends Controller
{
    public function store (Request $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->all());

        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
        ], 201);
    }
}
