<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatThread extends Model
{
    protected $table = 'chat_threads';

    protected $fillable = [
        'user_id',
        'partner_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_thread_id');
    }
}

