<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ChatThread extends Model
{
    use HasUuids;

    protected $table = 'chat_threads';

    protected $fillable = [
        'id',
        'report_id',
        'user_id',
        'mitra_id',
        'last_message_at',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'mitra_id');
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_thread_id');
    }
}
