<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ValidationRules;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use function App\Helpers\makeResponseWithCreated;

class MessageController extends Controller
{
    public function index()
    {
        $authUserProfileId = Profile::where('user_id', Auth::id())->firstOrFail()->id;
        $messages = Message::where('profile_id', $authUserProfileId)->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }

    public function create(Request $request)
    {
        return makeResponseWithCreated('Message', function () use ($request) {
            $validated = $request->validate(ValidationRules::message());
            $user = User::where($validated['doctor_details'])->with('profile')->firstOrFail();

            return Message::create([
                ...$validated,
                'profile_id' => $user->profile->id
            ]);
        });
    }

}
