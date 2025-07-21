<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle a login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'string', 'email'],
                'password' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                Log::error('Login validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Cerca l'utente con l'email fornita
            $user = User::where('email', $validated['email'])->first();

            try {
                $request_full_name = $user->first_name . $user->last_name;
                $request_pwd = $validated['password'];
                Log::info("User's password hashed: " . Hash::make($request_pwd) . ', database counterpart: ' . $user->password);
                if (Hash::check($request_pwd, $user->password)) {
                    Log::info("Authenticated user: $request_full_name, id: {$user->id}");

                    // Log in the user and establish the session
                    Auth::login($user, $remember = true);

                    // Regenerate the session to prevent session fixation attacks
                    $request->session()->regenerate();

                    // Retrieve related profile_id if present
                    // $profile_id = Profile::select('id')->where('user_id', $user->id)->get();

                    return response()->json([
                        'message' => 'User authenticated',
                        /* 'user' => [
                            'id' => $user->id,
                            'profile_id' => $profile_id[0]->id,
                        ] */
                    ]);
                } else {
                    throw new Exception('invalid password');
                }
            } catch (Exception $e) {
                Log::info("Failed authentication: invalid credentials ({$e->getMessage()})");
                return response()->json([
                    'message' => 'Failed authentication: invalid credentials'
                ], 401);
            }
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during login'
            ], 500);
        }
    }

    /**
     * Check the user authentication status via API
     *
     * @return function:void
     */
    public function checkLoginStatus()
    {
        $user_id = Auth::id();
        if ($user_id) {
            return response(['authentication' => [
                'status' => 'true',
            ]], 200);
        }

        return response(['authentication' => [
            'status' => 'false',
        ]], 404);
    }

    /**
     * Handle a logout request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        try {
            // For future token implementations
            /* $request->user()->currentAccessToken()->delete(); */
            $user_id = $request->user()->id;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Log::info('User logged out successfully', ['userId' => $user_id]);

            return response()->json([
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during logout'
            ], 500);
        }
    }
}
