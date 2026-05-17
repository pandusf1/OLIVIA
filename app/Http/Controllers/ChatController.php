<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Partner;
use App\Models\UserPartnerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private function paidPartnerIds()
    {
        return UserPartnerPayment::query()
            ->where('user_id', auth()->id())
            ->where('status', 'paid')
            ->pluck('partner_id')
            ->unique()
            ->values();
    }

    private function paidThreads()
    {
        $partnerIds = $this->paidPartnerIds();
        $now = now();

        foreach ($partnerIds as $partnerId) {
            ChatThread::firstOrCreate(
                ['user_id' => auth()->id(), 'partner_id' => $partnerId],
                ['id' => (string) Str::uuid(), 'last_message_at' => $now]
            );
        }

        return ChatThread::query()
            ->where('user_id', auth()->id())
            ->whereIn('partner_id', $partnerIds)
            ->with('partner')
            ->orderByDesc('last_message_at')
            ->get();
    }

    private function hasPaidAccess(string $partnerId): bool
    {
        return UserPartnerPayment::query()
            ->where('user_id', auth()->id())
            ->where('partner_id', $partnerId)
            ->where('status', 'paid')
            ->exists();
    }

    public function start(string $partnerId)
    {
        if (!$this->hasPaidAccess($partnerId)) {
            return redirect()
                ->route('partner.data', ['partnerId' => $partnerId])
                ->with('success', 'Pilih layanan dan selesaikan pembayaran dulu untuk membuka chat.');
        }

        $userId = auth()->id();

        $thread = ChatThread::query()
            ->where('user_id', $userId)
            ->where('partner_id', $partnerId)
            ->first();

        if (!$thread) {
            $thread = ChatThread::create([
                'id'             => (string) Str::uuid(),
                'user_id'        => $userId,
                'partner_id'     => $partnerId,
                'last_message_at'=> now(),
            ]);
        }

        $messages = $thread->messages()->latest()->get()->reverse()->values();
        $partner  = Partner::find($partnerId);

        return view('pages.user.chat', [
            'partnerId' => $partnerId,
            'threadId'  => $thread->id,
            'messages'  => $messages,
            'partner'   => $partner,
            'threads'   => $this->paidThreads(),
        ]);
    }

    public function indexThreads()
    {
        return view('pages.user.chat', [
            'partnerId' => null,
            'threadId' => null,
            'messages' => collect(),
            'partner' => null,
            'threads' => $this->paidThreads(),
        ]);
    }

    /**
     * JSON endpoint: fetch messages for the widget
     */
    public function messages(string $partnerId)
    {
        if (!$this->hasPaidAccess($partnerId)) {
            return response()->json(['messages' => []], 403);
        }

        $thread = ChatThread::query()
            ->where('user_id', auth()->id())
            ->where('partner_id', $partnerId)
            ->first();

        if (!$thread) {
            return response()->json(['messages' => []]);
        }

        $messages = $thread->messages()
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(fn($m) => [
                'sender_type' => $m->sender_type,
                'message'     => $m->message,
                'time'        => $m->created_at->format('H:i'),
            ]);

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request, string $partnerId)
    {
        if (!$this->hasPaidAccess($partnerId)) {
            return redirect()
                ->route('partner.data', ['partnerId' => $partnerId])
                ->with('success', 'Pilih layanan dan selesaikan pembayaran dulu untuk membuka chat.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $thread = ChatThread::firstOrCreate(
            ['user_id' => auth()->id(), 'partner_id' => $partnerId],
            ['id' => (string) Str::uuid(), 'last_message_at' => now()]
        );

        ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'sender_type'    => 'user',
            'sender_id'      => auth()->id(),
            'message'        => $request->message,
        ]);

        $thread->update(['last_message_at' => now()]);

        // Return JSON for widget requests, HTML for normal form submissions
        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['ok' => true]);
        }

        $messages = $thread->messages()->latest()->get()->reverse()->values();
        $partner  = Partner::find($partnerId);

        return view('pages.user.chat', [
            'partnerId' => $partnerId,
            'threadId'  => $thread->id,
            'messages'  => $messages,
            'partner'   => $partner,
            'threads'   => $this->paidThreads(),
        ]);
    }
}
