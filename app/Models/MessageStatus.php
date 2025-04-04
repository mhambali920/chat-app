<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageStatus extends Model
{
    protected $table = "message_status";
    protected $fillable = ['message_id', 'user_id', 'status'];
}
