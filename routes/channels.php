<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Private chat channel for authenticated users
Broadcast::channel('chat-room.{roomId}', function ($user, $roomId) {
    return auth()->check();
});

// Presence channel to track online users in chat rooms
Broadcast::channel('presence-chat-room.{roomId}', function ($user, $roomId) {
    return ['id' => $user->id, 'name' => $user->name];
});
