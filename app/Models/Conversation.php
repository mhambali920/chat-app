<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'is_group',
        'photo'
    ];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
