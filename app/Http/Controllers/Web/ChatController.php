<?php

namespace App\Http\Controllers\Web;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\MessageStatus;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('chat/index', [
            'users' => User::where('id', '!=', $user->id)->get(),
            'conversations' => $user->conversations()->with('users')->get(),
        ]);
    }

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

        return redirect()->route('chat.show', $conversation);
    }

    public function show(Conversation $conversation)
    {
        // Verifikasi user adalah anggota percakapan
        // $this->authorize('view', $conversation);

        return Inertia::render('chat/show', [
            'conversation' => $conversation,
            'messages' => Message::where('conversation_id', $conversation->id)
                ->with('user')
                ->latest()
                ->paginate(20),
        ]);
    }

    public function storeConversation(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'name' => 'required|string|max:255',
        ]);

        $conversation = Conversation::create([
            'name' => $validated['name'],
        ]);


        // Tambahkan user yang login dan pastikan tidak duplikat
        $userIds = array_unique(array_merge([$request->user()->id], $validated['user_ids']));
        $conversation->users()->attach($userIds);
        dd($conversation);

        // return redirect()->route('chat.show', $conversation)
        //     ->with('success', 'Percakapan berhasil dibuat.');
        return redirect()->back()->with('success', 'Percakapan berhasil dibuat.');
    }

}
