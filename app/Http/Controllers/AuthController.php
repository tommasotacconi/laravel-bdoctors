<?php

namespace App\Http\Controllers;

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
                    return response()->json([
                        'message' => 'User authenticated',
                        'user_id' => $user->id
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
            } */

            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User logged in successfully', ['user_id' => $user->id]);

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'An error occurred during login'
            ], 500);
        }

        $request->validate([
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
        }

        return response()->json(['error' => 'Unauthorized'], 401);
        // Valida le credenziali
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        // Cerca l'utente con l'email fornita
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Credenziali non valide'], 401);
        }

        // Autentica l'utente usando la sessione
        Auth::login($user);

        // Restituisce una risposta con il messaggio di successo
        return response()->json(['message' => 'Logged in successfully MARTE']);
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
            $request->user()->currentAccessToken()->delete();

            Log::info('User logged out successfully', ['user_id' => $request->user()->id]);

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
