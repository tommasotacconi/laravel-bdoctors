<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateMessageController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = $this->validateMessageData($request);

            $newMessage = new Message();

            $newMessage->profile_id = $validated['profile_id'];
            $newMessage->content = $validated['content'];
            $newMessage->email = $validated['email'];
            $newMessage->first_name = $validated['first_name'];
            $newMessage->last_name = $validated['last_name'];




            $newMessage->save();

            Log::info('Profile created successfully', ['message_id' => $newMessage->id]);

            return response()->json([
                'message' => 'Message created successfully',
                'profile' => $newMessage
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Profile creation validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Profile creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Message creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate profile data
     *
     * @param Request $request
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateMessageData(Request $request)
    {
        return $request->validate([
            'profile_id' => ['required', 'exists:profiles,id'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => [
                'required',
                'string',
                'email',
                'max:50',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com|it|org|net|edu|gov)$/'
            ],
            'content' => ['required', 'string', 'min:5', 'max:300']
        ]);
    }
}
