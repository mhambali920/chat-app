<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    // Hanya user yang terlibat dalam percakapan yang dapat join channel
    if ($conversation) {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }

    return false;
}, ['guards' => ['api', 'web']]);
