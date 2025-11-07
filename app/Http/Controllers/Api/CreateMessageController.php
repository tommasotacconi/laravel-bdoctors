<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CreateMessageController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated = $this->validateMessageData($request);

            $user = User::where([
                ['homonymous_id', $validated['doctor_details']['homonymous_id'] ?? null] ,
                ['first_name', $validated['doctor_details']['first_name']],
                ['last_name', $validated['doctor_details']['last_name']],
            ])->with('profile')->firstOrFail();
            $newMessage = new Message();

            $newMessage->profile_id = $user->profile->id;
            $newMessage->content = $validated['content'];
            $newMessage->email = $validated['email'];
            $newMessage->first_name = $validated['first_name'];
            $newMessage->last_name = $validated['last_name'];

            $newMessage->save();

            Log::info('Message created successfully', ['message_id' => $newMessage->id]);

            return response()->json([
                'message' => 'Message created successfully',
                'profile' => $newMessage
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Message creation validation failed', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Message creation failed', [
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
        return $request->validate(ValidationRules::message());
    }
}
