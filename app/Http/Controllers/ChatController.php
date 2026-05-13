<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function start(string $partnerId)
    {
        // PostgreSQL + uuid PK: untuk menghindari edge-case null id saat insert pertama,
        // selalu buat record jika belum ada dengan id eksplisit.
        $userId = auth()->id();

        $thread = ChatThread::query()
            ->where('user_id', $userId)
            ->where('partner_id', $partnerId)
            ->first();

        if (!$thread) {
            $thread = ChatThread::create([
                // Pastikan id UUID terisi saat insert
                // (uuid PK NOT NULL di PostgreSQL)
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'partner_id' => $partnerId,
                'last_message_at' => now(),
            ]);
        }




        $messages = $thread->messages()->latest()->get()->reverse()->values();

        return view('pages.user.chat', [
            'partnerId' => $partnerId,
            'threadId' => $thread->id,
            'messages' => $messages,
        ]);
    }

    public function indexThreads()
    {
        $threads = ChatThread::query()
            ->where('user_id', auth()->id())
            ->with('partner')
            ->orderByDesc('last_message_at')
            ->get();

        return view('pages.user.chat_threads', ['threads' => $threads]);
    }

    public function send(Request $request, string $partnerId)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $thread = ChatThread::firstOrCreate(
            ['user_id' => auth()->id(), 'partner_id' => $partnerId],
            ['last_message_at' => now()]
        );

        ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $thread->update(['last_message_at' => now()]);

        $messages = $thread->messages()->latest()->get()->reverse()->values();

        return view('pages.user.chat', [
            'partnerId' => $partnerId,
            'threadId' => $thread->id,
            'messages' => $messages,
        ]);
    }
}
