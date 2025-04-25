<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{userId}', function (User $user, $userId) {
    // Log for debugging:
    Log::info("Auth check for chat.{$userId}: Auth User ID = {$user->id}, Target User ID = {$userId}");

    // Check: Is the user authenticated? If yes, allow access for now.
    // In a real app, you'd add more checks (e.g., are they friends? Is there a conversation?)
    $is_authorized = $user !== null;

    Log::info("Authorization result: " . ($is_authorized ? 'true' : 'false'));
    return $is_authorized;
});
