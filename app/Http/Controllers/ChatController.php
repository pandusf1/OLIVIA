<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Partner;
use App\Models\Report;
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

    private function reportContext(?string $reportId): ?Report
    {
        if (!$reportId) {
            return null;
        }

        return Report::with(['user', 'evidences', 'partnerRoutings.partner'])->find($reportId);
    }

    private function hasEmergencyChatAccess(string $partnerId, ?Report $report): bool
    {
        if (!$report) {
            return false;
        }

        $user = auth()->user();

        if ($user->role === 'partner') {
            return (string) $user->partner_id === (string) $partnerId
                && $report->partnerRoutings()
                    ->where('partner_id', $partnerId)
                    ->where('status', 'accepted')
                    ->exists();
        }

        return (string) $report->user_id === (string) $user->id
            && $report->partnerRoutings()
                ->where('partner_id', $partnerId)
                ->where('status', 'accepted')
                ->exists();
    }

    private function canChat(string $partnerId, ?Report $report = null, ?string $userId = null): bool
    {
        if ($this->hasPaidAccess($partnerId, $userId)) {
            return true;
        }
        return $this->hasEmergencyChatAccess($partnerId, $report);
    }

    private function hasPaidAccess(string $partnerId, ?string $userId = null): bool
    {
        $checkUserId = auth()->user()->role === 'partner' ? $userId : auth()->id();
        
        if (!$checkUserId) {
            return false;
        }

        return UserPartnerPayment::query()
            ->where('user_id', $checkUserId)
            ->where('partner_id', $partnerId)
            ->where('status', 'paid')
            ->exists();
    }

    private function reportContext(?string $reportId): ?Report
    {
        if (!$reportId) {
            return null;
        }

        return Report::with(['user', 'evidences', 'partnerRoutings.partner'])->find($reportId);
    }

    private function hasEmergencyChatAccess(string $partnerId, ?Report $report): bool
    {
        if (!$report) {
            return false;
        }

        $user = auth()->user();

        if ($user->role === 'partner') {
            return (string) $user->partner_id === (string) $partnerId
                && $report->partnerRoutings()
                    ->where('partner_id', $partnerId)
                    ->where('status', 'accepted')
                    ->exists();
        }

        return (string) $report->user_id === (string) $user->id
            && $report->partnerRoutings()
                ->where('partner_id', $partnerId)
                ->where('status', 'accepted')
                ->exists();
    }

    private function currentThreads()
    {
        $user = auth()->user();

        if ($user->role === 'partner') {
            return ChatThread::query()
                ->where('partner_id', $user->partner_id)
                ->with(['partner', 'user'])
                ->orderByDesc('last_message_at')
                ->get()
                ->map(function ($thread) {
                    $report = Report::query()
                        ->where('user_id', $thread->user_id)
                        ->whereHas('partnerRoutings', function ($query) use ($thread) {
                            $query->where('partner_id', $thread->partner_id)
                                ->where('status', 'accepted');
                        })
                        ->latest('updated_at')
                        ->first();

                    $thread->report_context_id = $report?->id;
                    $thread->is_anonymous = $report?->anonymous ?? false;

                    return $thread;
                });
        }

        return $this->paidThreads();
    }

    private function threadFor(string $partnerId, ?Report $report = null, ?string $userId = null): ?ChatThread
    {
        $uId = auth()->id();

        if (auth()->user()->role === 'partner') {
            $uId = $report ? $report->user_id : $userId;
        }

        if (!$uId) {
            return null;
        }

        return ChatThread::query()
            ->where('user_id', $uId)
            ->where('partner_id', $partnerId)
            ->first();
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
            'threads'   => $this->currentThreads(),
            'reportContext' => null,
            'viewerType' => 'user',
        ]);
    }

    public function indexThreads()
    {
        return view('pages.user.chat', [
            'partnerId' => null,
            'threadId' => null,
            'messages' => collect(),
            'partner' => null,
            'threads' => $this->currentThreads(),
            'reportContext' => null,
            'viewerType' => auth()->user()->role === 'partner' ? 'partner' : 'user',
        ]);
    }

    /**
     * JSON endpoint: fetch messages for the widget
     */
    public function messages(string $partnerId)
    {
        $report = $this->reportContext(request('report_id'));
        $userId = request('user_id');

        if (!$this->canChat($partnerId, $report, $userId)) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['messages' => []], 403);
            }

            return redirect()->route('chat.threads')->with('success', 'Akses chat tidak tersedia.');
        }

        $thread = $this->threadFor($partnerId, $report, $userId);

        if (!$thread && (request()->ajax() || request()->expectsJson())) {
            return response()->json(['messages' => []]);
        }

        if (!$thread) {
            return redirect()->route('chat.threads')->with('success', 'Thread chat belum tersedia.');
        }

        if (!(request()->ajax() || request()->expectsJson())) {
            return view('pages.user.chat', [
                'partnerId' => $partnerId,
                'threadId' => $thread->id,
                'messages' => $thread->messages()->latest()->get()->reverse()->values(),
                'partner' => Partner::find($partnerId),
                'threads' => $this->currentThreads(),
                'reportContext' => $report,
                'viewerType' => auth()->user()->role === 'partner' ? 'partner' : 'user',
            ]);
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
        $report = $this->reportContext($request->query('report_id'));
        $userId = $request->query('user_id');

        if (!$this->canChat($partnerId, $report, $userId)) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['ok' => false], 403);
            }

            return redirect()
                ->route('partner.data', ['partnerId' => $partnerId])
                ->with('success', 'Pilih layanan dan selesaikan pembayaran dulu untuk membuka chat.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $senderType = auth()->user()->role === 'partner' ? 'partner' : 'user';
        $senderId = $senderType === 'partner' ? auth()->user()->partner_id : auth()->id();
        $threadUserId = $senderType === 'partner' ? ($report ? $report->user_id : $userId) : auth()->id();

        if (!$threadUserId) {
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['ok' => false], 422);
            }

            return back()->with('success', 'Chat belum bisa dibuka karena laporan tidak memiliki user pelapor.');
        }

        $thread = ChatThread::firstOrCreate(
            ['user_id' => $threadUserId, 'partner_id' => $partnerId],
            ['id' => (string) Str::uuid(), 'last_message_at' => now()]
        );

        ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'sender_type'    => $senderType,
            'sender_id'      => $senderId,
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
            'threads'   => $this->currentThreads(),
            'reportContext' => $report,
            'viewerType' => $senderType,
        ]);
    }
}
