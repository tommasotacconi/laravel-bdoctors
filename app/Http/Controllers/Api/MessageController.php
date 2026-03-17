<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\RespondsWithApi;
use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use App\Validation\BaseValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    use RespondsWithApi;

    public function __construct(protected Request $req, protected BaseValidation $bV) {}

    public function index()
    {
        $authProfile = Profile::where('user_id', Auth::id())->firstOrFail();
        $messages = Message::where('profile_id', $authProfile->id)->orderByDesc('created_at')->get();

        return $this->apiResponse(
            $messages,
            'messages',
        );
    }

    public function create()
    {
        $validated = $this->req->validate($this->bV::message());
        $user = User::where($validated['doctor_details'])->with('profile')->firstOrFail();

        return $this->apiResponse(
            Message::create([
                ...$validated,
                'profile_id' => $user->profile->id
            ]),
            'message_resource',
            'message sent'
        );
    }
}
