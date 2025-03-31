<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conversation;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConversationPolicy
{
    use HandlesAuthorization;
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Cek apakah user dapat melihat percakapan.
     */
    public function view(User $user, Conversation $conversation)
    {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Cek apakah user dapat mengirim pesan dalam percakapan.
     */
    public function sendMessage(User $user, Conversation $conversation)
    {
        return $conversation->users()->where('user_id', $user->id)->exists();
    }
}
