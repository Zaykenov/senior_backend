<?php

namespace App\Http\Controllers\API;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Get the latest messages for a specific room
     * 
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($roomId)
    {
        $room = Room::findOrFail($roomId);
        
        $messages = $room->messages()
            ->with('user')  // Eager load the user relation
            ->latest()      // Most recent first
            ->limit(50)     // Only get the last 50 messages
            ->get()
            ->sortBy('created_at'); // Display in chronological order
        
        return response()->json($messages);
    }
    
    /**
     * Store a new chat message and broadcast it
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'body' => 'required|string|max:1000',
        ]);
        
        $message = Message::create([
            'room_id' => $validated['room_id'],
            'user_id' => Auth::id(), // Current authenticated user
            'body' => $validated['body'],
        ]);
        
        // Load the relationship so it's included in the event
        $message->load('user');
        
        // Broadcast the MessageSent event
        event(new MessageSent($message));
        
        return response()->json($message, 201);
    }
}