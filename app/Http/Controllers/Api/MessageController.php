<?php

namespace App\Http\Controllers\Api;

use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\MessageStatus;
use App\Events\MessageDeleted;
use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;


class MessageController extends Controller
{
    /**
     * Mendapatkan daftar pesan untuk percakapan tertentu
     */
    public function index(Conversation $conversation)
    {
        // Verifikasi user adalah anggota percakapan
        // $this->authorize('view', $conversation);

        $messages = $conversation->messages()
            ->with('user')
            ->latest()
            ->paginate(20);

        // Update status pesan menjadi read
        foreach ($messages->items() as $message) {
            if ($message->user_id !== auth()->id()) {
                MessageStatus::updateOrCreate(
                    [
                        'message_id' => $message->id,
                        'user_id' => auth()->id(),
                    ],
                    ['status' => 'read']
                );
            }
        }

        return MessageResource::collection($messages);
    }

    /**
     * Menyimpan pesan baru
     */
    public function store(Request $request, Conversation $conversation)
    {
        // Verifikasi user adalah anggota percakapan
        // $this->authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'body' => 'nullable|string',
            'type' => 'required|in:text,image,video,audio,document,location',
            'file' => 'nullable|file|max:20480', // Max 20MB
            'latitude' => 'nullable|required_if:type,location|numeric',
            'longitude' => 'nullable|required_if:type,location|numeric',
        ]);

        $message = new Message();
        $message->conversation_id = $conversation->id;
        $message->user_id = auth()->id();
        $message->type = $validated['type'];

        if ($validated['type'] === 'text') {
            $message->body = $validated['body'];
        } elseif ($validated['type'] === 'location') {
            $message->body = json_encode([
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude']
            ]);
        } elseif ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat_files/' . $conversation->id);
            $message->file_path = $path;
            $message->body = $request->input('body'); // Caption opsional
        }

        $message->save();

        // Tambahkan status pesan 'sent' untuk semua anggota percakapan
        foreach ($conversation->users as $user) {
            if ($user->id !== auth()->id()) {
                MessageStatus::create([
                    'message_id' => $message->id,
                    'user_id' => $user->id,
                    'status' => 'sent'
                ]);
            }
        }

        // Broadcast event
        broadcast(new MessageSent($message, $conversation))->toOthers();

        return new MessageResource($message);
    }

    /**
     * Menghapus pesan
     */
    public function destroy(Message $message)
    {
        // Verifikasi bahwa pengguna adalah pengirim pesan atau admin grup
        // $this->authorize('delete', $message);

        // Gunakan soft delete
        $message->delete();

        // Broadcast event
        broadcast(new MessageDeleted($message, $message->conversation))->toOthers();

        return response()->json(['message' => 'Message deleted successfully']);
    }

    /**
     * Membalas pesan
     */
    public function reply(Request $request, Message $message)
    {
        $conversation = $message->conversation;

        // Verifikasi user adalah anggota percakapan
        // $this->authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'body' => 'required|string',
            'type' => 'required|in:text,image,video,audio,document,location',
            'file' => 'nullable|file|max:20480', // Max 20MB
        ]);

        $reply = new Message();
        $reply->conversation_id = $conversation->id;
        $reply->user_id = auth()->id();
        $reply->type = $validated['type'];

        // Simpan informasi pesan yang dibalas
        $replyData = [
            'replied_to_id' => $message->id,
            'replied_to_user_id' => $message->user_id,
            'replied_to_body' => $message->body,
        ];

        if ($validated['type'] === 'text') {
            $reply->body = $validated['body'];
        } elseif ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat_files/' . $conversation->id);
            $reply->file_path = $path;
            $reply->body = $request->input('body'); // Caption opsional
        }

        // Simpan data reply dalam format JSON di body
        $reply->body = json_encode([
            'content' => $reply->body,
            'reply_to' => $replyData
        ]);

        $reply->save();

        // Tambahkan status pesan 'sent' untuk semua anggota percakapan
        foreach ($conversation->users as $user) {
            if ($user->id !== auth()->id()) {
                MessageStatus::create([
                    'message_id' => $reply->id,
                    'user_id' => $user->id,
                    'status' => 'sent'
                ]);
            }
        }

        // Broadcast event
        broadcast(new MessageSent($reply, $conversation))->toOthers();

        return new MessageResource($reply);
    }
}
