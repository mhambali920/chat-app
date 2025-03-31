<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'profile_photo' => $this->user->profile_photo,
            ],
            'type' => $this->type,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];

        // Handle berbagai tipe pesan
        if ($this->type === 'text') {
            $data['body'] = $this->body;
        } elseif ($this->type === 'location') {
            $data['location'] = json_decode($this->body);
        } elseif (in_array($this->type, ['image', 'video', 'audio', 'document'])) {
            $data['file_url'] = $this->file_path ? url('storage/' . $this->file_path) : null;
            $data['caption'] = $this->body;
        }

        // Cek apakah ini adalah pesan balasan
        if ($this->body && is_string($this->body) && json_decode($this->body)) {
            $decoded = json_decode($this->body, true);
            if (isset($decoded['reply_to'])) {
                $data['reply_to'] = $decoded['reply_to'];
                $data['body'] = $decoded['content'] ?? '';
            }
        }

        // Status pesan
        $data['statuses'] = $this->statuses->map(function ($status) {
            return [
                'user_id' => $status->user_id,
                'status' => $status->status,
                'updated_at' => $status->updated_at->toIso8601String(),
            ];
        });

        return $data;
    }
}
