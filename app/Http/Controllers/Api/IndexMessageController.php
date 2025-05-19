<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexMessageController extends Controller
{
    public function index()
    {
        $authenticatedUserId = Auth::id();
        $authenticatedUserProfileId = Profile::where('user_id', $authenticatedUserId)->firstOrFail()->id;
        $messages = Message::where('profile_id', $authenticatedUserProfileId)->with(['profiles'])->get();
        //$messages = Message::all();
        //dd($messages);
        return response()->json([
            'success' => true,
            'messages' => $messages
        ]);
    }
}
