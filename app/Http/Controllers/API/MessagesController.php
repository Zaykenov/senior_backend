<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagesController extends Controller
{
    /**
     * List all messages in a room.
     */
    public function index($roomId)
    {
        $room = Room::findOrFail($roomId);
        $messages = $room->messages()
            ->with('user')
            ->orderBy('created_at')
            ->get();
        return response()->json($messages);
    }

    /**
     * Store a new message and broadcast it.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'body'    => 'required|string',
        ]);

        $message = Message::create([
            'room_id' => $validated['room_id'],
            'user_id' => Auth::id(),
            'body'    => $validated['body'],
        ]);

        event(new MessageSent($message));

        return response()->json($message, 201);
    }
}