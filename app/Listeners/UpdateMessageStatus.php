<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Models\MessageStatus;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateMessageStatus implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        // Update semua status pesan menjadi 'delivered' ketika event ditangani
        // Ini akan berjalan sebagai queue job
        $messageStatuses = MessageStatus::where('message_id', $event->message->id)
            ->where('status', 'sent')
            ->get();

        foreach ($messageStatuses as $status) {
            $status->status = 'delivered';
            $status->save();
        }
    }
}
