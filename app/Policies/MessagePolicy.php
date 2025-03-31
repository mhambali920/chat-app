<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Message;
use Illuminate\Auth\Access\HandlesAuthorization;

class MessagePolicy
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
     * Cek apakah user dapat menghapus pesan.
     */
    public function delete(User $user, Message $message)
    {
        // User adalah pengirim pesan
        if ($user->id === $message->user_id) {
            return true;
        }

        // Atau user adalah admin grup
        $conversation = $message->conversation;
        if ($conversation->is_group) {
            return $conversation->users()
                ->where('user_id', $user->id)
                ->wherePivot('is_admin', true)
                ->exists();
        }

        return false;
    }
}
