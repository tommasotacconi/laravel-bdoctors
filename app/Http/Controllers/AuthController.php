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
                if ($request_pwd === $user->password) {
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

            /* if (!Auth::attempt($request->only('email', 'password'))) {
                Log::warning('Failed login attempt', ['email' => $request->email]);
                return response()->json([
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User logged in successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]); */
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during login'
            ], 500);
        }

        // Previous validation logic
        /* $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Verifica che l'utente esista e che la password sia corretta
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // // Effettua l'autenticazione e crea il token
        Auth::login($user);

        return response()->json(['message' => 'Logged in successfully']);
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Se usi Laravel Sanctum, puoi generare un token come questo:
            $token = $user->createToken('YourAppName')->plainTextToken;
            Auth::login($user);  // Autenticazione tramite sessione
            return response()->json(['message' => 'Logged in successfully']);

            return response()->json([
                'message' => 'Logged in successfully',
                'token' => $token
            ]);
        } */
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
                'userId' => $user_id
            ]], 200);
        }

        return response(['authentication' => [
            'status' => 'false',
            'userId' => null
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
