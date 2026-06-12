<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Mitra;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    /**
     * Cek apakah user/guest boleh masuk ke chat laporan ini.
     *
     * Akses TERBUKA dari awal untuk:
     * 1. Pelapor (user_id === report.user_id, atau via session my_reports)
     * 2. Warga dalam radius < 5 km dari lokasi laporan (lat/lng via query param)
     *
     * Mitra bisa join chat setelah menerima (accept) laporan.
     * Mitra yang belum accept tidak bisa masuk.
     */
    private function canAccessReportChat(Report $report, ?float $lat = null, ?float $lng = null): bool
    {
        $user = auth()->user();

        // Mitra: mitra yang di-route (baik pending, accepted, expired) diizinkan membaca chat
        if ($user && $user->role === 'mitra') {
            return $report->mitraRoutings()
                ->where('mitra_id', $user->mitra_id)
                ->exists();
        }

        // Pelapor login
        if ($user && $report->user_id && (string) $user->id === (string) $report->user_id) {
            return true;
        }

        // Kontak Terpercaya login
        if ($user && $report->user_id && $user->phone) {
            $isTrustedContact = \App\Models\TrustedContact::where('user_id', $report->user_id)
                ->where('contact_phone', $user->phone)
                ->where('is_verified', true)
                ->exists();
            if ($isTrustedContact) {
                return true;
            }
        }

        // Pelapor via session/cookie (anonymous report)
        $cookieReports = request()->hasCookie('safora_my_reports')
            ? json_decode(request()->cookie('safora_my_reports'), true) ?: []
            : [];
        if (in_array($report->id, session()->get('my_reports', [])) || in_array($report->id, $cookieReports)) {
            try {
                $sessionReports = session()->get('my_reports', []);
                if (!in_array($report->id, $sessionReports)) {
                    $sessionReports[] = $report->id;
                    session()->put('my_reports', $sessionReports);
                }
            } catch (\Exception $e) {}
            return true;
        }

        // Warga/saksi: dalam radius 5 km dari lokasi laporan
        if ($lat !== null && $lng !== null && $report->latitude && $report->longitude) {
            $dist = $this->haversineKm($lat, $lng, (float) $report->latitude, (float) $report->longitude);
            if ($dist <= 5.0) {
                return true;
            }
        }

        return false;
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Tentukan nama pengirim untuk tampilan chat.
     */
    private function senderDisplayName(ChatMessage $msg, Report $report): string
    {
        if ($msg->sender_type === 'mitra') {
            $mitra = Mitra::find($msg->sender_id);
            return $mitra ? $mitra->mitra_name : 'Mitra Safora';
        }

        // Cek jika pelapor via cache (guest reporter)
        $reporterUuid = \Illuminate\Support\Facades\Cache::get("report_{$report->id}_reporter_uuid");

        $isReporter = ($report->user_id && (string) $msg->sender_id === (string) $report->user_id)
            || ($reporterUuid && (string) $msg->sender_id === (string) $reporterUuid);

        if ($isReporter) {
            if ($msg->sender_type === 'user' || $msg->sender_type === 'mitra_user') {
                $user = \App\Models\User::find($msg->sender_id);
                $name = $user ? $user->name : 'anonim';
                return "Korban ({$name})";
            }
            return 'Korban (anonim)';
        }

        // Warga/saksi biasa (non-reporter)
        if ($msg->sender_type === 'anonymous' || !$msg->sender_id) {
            return 'Saksi (anonim)';
        }

        $user = \App\Models\User::find($msg->sender_id);
        return $user ? "Saksi ({$user->name})" : 'Saksi (anonim)';
    }

    /**
     * Dapatkan UUID anonim yang persisten dari cookie atau session.
     */
    private function getAnonymousChatUuid(): string
    {
        $uuid = request()->cookie('safora_anonymous_chat_uuid');

        if (!$uuid) {
            $uuid = session()->get('anonymous_chat_uuid');
        }

        if (!$uuid) {
            $uuid = (string) Str::uuid();
        }

        if (session()->get('anonymous_chat_uuid') !== $uuid) {
            session()->put('anonymous_chat_uuid', $uuid);
        }

        cookie()->queue(cookie('safora_anonymous_chat_uuid', $uuid, 60 * 24 * 30));

        return $uuid;
    }

    /**
     * Halaman chat laporan (GET /chat/report/{reportId})
     */
    public function reportChat(string $reportId)
    {
        $report = Report::with([
            'user',
            'mitraRoutings.mitra',
            'timelineEvents',
        ])->findOrFail($reportId);

        $lat  = request()->query('lat')  ? (float) request()->query('lat')  : null;
        $lng  = request()->query('lng')  ? (float) request()->query('lng')  : null;

        if (!$this->canAccessReportChat($report, $lat, $lng)) {
            abort(403, 'Akses chat tidak diizinkan. Kamu harus berada dalam radius 5 km dari lokasi kejadian, atau merupakan pelapor/mitra yang menangani.');
        }

        // Cek jika pelapor adalah guest, simpan anonymous_chat_uuid di cache agar teridentifikasi sebagai Korban
        if (in_array($report->id, session()->get('my_reports', []))) {
            $anonUuid = $this->getAnonymousChatUuid();
            \Illuminate\Support\Facades\Cache::forever("report_{$report->id}_reporter_uuid", $anonUuid);
        }

        // Buat/ambil thread untuk laporan ini
        $thread = ChatThread::firstOrCreate(
            ['report_id' => $report->id],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $report->user_id,
                'last_message_at' => now()
            ]
        );

        $rawMessages = $thread->messages()->orderBy('created_at', 'asc')->get();

        $messages = $rawMessages->map(function ($msg) use ($report) {
            $user = auth()->user();
            $isMine = false;

            if ($user && $user->role === 'mitra') {
                $isMine = $msg->sender_type === 'mitra' && (string) $msg->sender_id === (string) $user->mitra_id;
            } elseif ($user) {
                $isMine = $msg->sender_type !== 'mitra' && (string) $msg->sender_id === (string) $user->id;
            } else {
                // Guest: identifikasi via session UUID
                $anonUuid = $this->getAnonymousChatUuid();
                $isMine = $msg->sender_type === 'anonymous' && $msg->sender_id === $anonUuid;
            }

            return [
                'id'          => $msg->id,
                'message'     => $msg->message,
                'sender_type' => $msg->sender_type,
                'sender_name' => $this->senderDisplayName($msg, $report),
                'time'        => $msg->created_at->format('H:i'),
                'date'        => $msg->created_at->format('d M'),
                'is_mine'     => $isMine,
            ];
        });

        // Identitas pengirim saat ini
        $user = auth()->user();
        if ($user && $user->role === 'mitra') {
            $currentSenderType = 'mitra';
            $currentSenderId   = $user->mitra_id;
            $currentName       = Mitra::find($user->mitra_id)?->mitra_name ?? 'Mitra';
        } elseif ($user) {
            $currentSenderType = 'user';
            $currentSenderId   = $user->id;
            $isReporter        = $report->user_id && (string) $user->id === (string) $report->user_id;
            $currentName       = $isReporter ? "Korban ({$user->name})" : "Saksi ({$user->name})";
        } else {
            $currentSenderType = 'anonymous';
            $anonUuid = $this->getAnonymousChatUuid();
            $currentSenderId   = $anonUuid;
            
            $isGuestReporter = in_array($report->id, session()->get('my_reports', []));
            $currentName       = $isGuestReporter ? 'Korban (anonim)' : 'Saksi (anonim)';
        }

        return view('pages.chat', [
            'report'            => $report,
            'thread'            => $thread,
            'messages'          => $messages,
            'currentSenderType' => $currentSenderType,
            'currentSenderId'   => $currentSenderId,
            'currentName'       => $currentName,
            'userLat'           => $lat,
            'userLng'           => $lng,
        ]);
    }

    /**
     * Kirim pesan (POST /chat/report/{reportId}/send)
     */
    public function sendMessage(Request $request, string $reportId)
    {
        $report = Report::with(['user', 'mitraRoutings'])->findOrFail($reportId);

        $lat = $request->query('lat') ? (float) $request->query('lat') : null;
        $lng = $request->query('lng') ? (float) $request->query('lng') : null;

        if (!$this->canAccessReportChat($report, $lat, $lng)) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false, 'message' => 'Akses ditolak.'], 403);
            }
            abort(403);
        }

        $request->validate(['message' => 'required|string|max:2000']);

        $user = auth()->user();
        if ($user && $user->role === 'mitra') {
            $isHandling = ($report->handler_mitra_id === $user->mitra_id);
            if (!$isHandling) {
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'message' => 'Akses ditolak. Anda tidak menangani kasus ini.'], 403);
                }
                abort(403, 'Akses ditolak. Anda tidak menangani kasus ini.');
            }
            $senderType = 'mitra';
            $senderId   = $user->mitra_id;
        } elseif ($user) {
            $senderType = 'user';
            $senderId   = $user->id;
        } else {
            $senderType = 'anonymous';
            $anonUuid = $this->getAnonymousChatUuid();
            $senderId   = $anonUuid;
            
            // Cek jika pelapor adalah guest, simpan di cache
            if (in_array($report->id, session()->get('my_reports', []))) {
                \Illuminate\Support\Facades\Cache::forever("report_{$report->id}_reporter_uuid", $senderId);
            }
        }

        $thread = ChatThread::firstOrCreate(
            ['report_id' => $report->id],
            [
                'id' => (string) Str::uuid(),
                'user_id' => $report->user_id,
                'last_message_at' => now()
            ]
        );

        ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'sender_type'    => $senderType,
            'sender_id'      => $senderId,
            'message'        => $request->message,
        ]);

        $thread->update(['last_message_at' => now()]);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['ok' => true]);
        }

        $redirectUrl = "/chat/report/{$reportId}";
        if ($lat && $lng) {
            $redirectUrl .= "?lat={$lat}&lng={$lng}";
        }
        return redirect($redirectUrl);
    }

    /**
     * JSON endpoint: poll messages (GET /chat/report/{reportId}/messages)
     */
    public function pollMessages(string $reportId)
    {
        $report = Report::findOrFail($reportId);

        $lat = request()->query('lat') ? (float) request()->query('lat') : null;
        $lng = request()->query('lng') ? (float) request()->query('lng') : null;

        if (!$this->canAccessReportChat($report, $lat, $lng)) {
            return response()->json(['messages' => []], 403);
        }

        $thread = ChatThread::where('report_id', $reportId)->first();
        if (!$thread) {
            return response()->json(['messages' => []]);
        }

        $user = auth()->user();
        $msgs = $thread->messages()->orderBy('created_at', 'asc')->get()->map(function ($msg) use ($report, $user) {
            $isMine = false;
            if ($user && $user->role === 'mitra') {
                $isMine = $msg->sender_type === 'mitra' && (string) $msg->sender_id === (string) $user->mitra_id;
            } elseif ($user) {
                $isMine = $msg->sender_type !== 'mitra' && (string) $msg->sender_id === (string) $user->id;
            } else {
                $anonUuid = $this->getAnonymousChatUuid();
                $isMine = $msg->sender_type === 'anonymous' && $msg->sender_id === $anonUuid;
            }

            return [
                'id'          => $msg->id,
                'message'     => $msg->message,
                'sender_name' => $this->senderDisplayName($msg, $report),
                'time'        => $msg->created_at->format('H:i'),
                'date'        => $msg->created_at->format('d M'),
                'is_mine'     => $isMine,
            ];
        });

        return response()->json(['messages' => $msgs]);
    }

    // Legacy methods
    // Direct User Chat methods

    public function indexThreads(Request $request)
    {
        $user = auth()->user();
        $isMitra = $user && $user->role === 'mitra';
        
        if ($isMitra) {
            $threads = ChatThread::with(['mitra', 'user', 'report'])
                ->where(function($query) use ($user) {
                    $query->where('mitra_id', $user->mitra_id)
                          ->orWhereHas('report', function($q) use ($user) {
                              $q->whereHas('mitraRoutings', function($pq) use ($user) {
                                  $pq->where('mitra_id', $user->mitra_id)
                                     ->where('status', 'accepted');
                              });
                          });
                })
                ->orderBy('last_message_at', 'desc')
                ->get();
            $viewerType = 'mitra';
        } else {
            $threads = ChatThread::with(['mitra', 'user', 'report'])
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereHas('report', function($q) use ($user) {
                              $q->where('user_id', $user->id);
                          });
                })
                ->orderBy('last_message_at', 'desc')
                ->get();
            $viewerType = 'user';
        }

        if ($request->expectsJson() || $request->ajax()) {
            $data = $threads->map(function ($t) use ($viewerType) {
                $threadName = $viewerType === 'mitra'
                    ? ($t->user?->name ?? 'Pelapor')
                    : ($t->mitra?->mitra_name ?? 'Mitra');
                
                $threadType = $viewerType === 'mitra'
                    ? 'Pelapor'
                    : match($t->mitra?->mitra_type ?? '') {
                        'ambulance' => 'Medis Darurat',
                        'legal' => 'Bantuan Hukum',
                        'counselor' => 'Psikososial',
                        'pemadam' => 'Pemadam / Rescue',
                        default => $t->mitra?->mitra_type ?? ''
                    };

                $threadHref = '#';
                if ($t->report_id) {
                    $threadHref = route('chat.report', ['reportId' => $t->report_id]);
                } elseif ($viewerType === 'mitra') {
                    $threadHref = route('chat.messages', ['mitraId' => $t->user_id]);
                } elseif ($t->mitra_id) {
                    $threadHref = route('chat.messages', ['mitraId' => $t->mitra_id]);
                }

                return [
                    'id' => $t->id,
                    'report_id' => $t->report_id,
                    'mitra_id' => $viewerType === 'mitra' ? $t->user_id : $t->mitra_id,
                    'user_id' => $t->user_id,
                    'threadName' => $threadName,
                    'threadType' => $threadType,
                    'threadHref' => $threadHref,
                    'mitra_image' => $t->mitra?->image_url ?? '',
                    'last_message_time' => $t->last_message_at ? $t->last_message_at->format('d M Y, H:i') : 'Belum ada pesan',
                ];
            });

            return response()->json([
                'threads' => $data,
                'viewerType' => $viewerType,
            ]);
        }

        return view('pages.user.chat', [
            'threads' => $threads,
            'mitraId' => null,
            'mitra' => null,
            'messages' => [],
            'viewerType' => $viewerType,
            'reportContext' => null,
        ]);
    }

    public function start(string $mitraId)
    {
        $user = auth()->user();
        
        $thread = ChatThread::firstOrCreate([
            'user_id' => $user->id,
            'mitra_id' => $mitraId,
        ], [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'last_message_at' => now(),
        ]);
        
        return redirect()->route('chat.messages', ['mitraId' => $mitraId]);
    }

    public function messages(string $mitraId)
    {
        $user = auth()->user();
        $isMitra = $user && $user->role === 'mitra';
        
        if ($isMitra) {
            $threads = ChatThread::with(['mitra', 'user', 'report'])
                ->where(function($query) use ($user) {
                    $query->where('mitra_id', $user->mitra_id)
                          ->orWhereHas('report', function($q) use ($user) {
                              $q->whereHas('mitraRoutings', function($pq) use ($user) {
                                  $pq->where('mitra_id', $user->mitra_id)
                                     ->where('status', 'accepted');
                              });
                          });
                })
                ->orderBy('last_message_at', 'desc')
                ->get();
                
            $thread = ChatThread::firstOrCreate([
                'user_id' => $mitraId,
                'mitra_id' => $user->mitra_id,
            ], [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'last_message_at' => now(),
            ]);
            
            $mitra = Mitra::find($user->mitra_id);
            $viewerType = 'mitra';
        } else {
            $threads = ChatThread::with(['mitra', 'user', 'report'])
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereHas('report', function($q) use ($user) {
                              $q->where('user_id', $user->id);
                          });
                })
                ->orderBy('last_message_at', 'desc')
                ->get();
                
            $thread = ChatThread::firstOrCreate([
                'user_id' => $user->id,
                'mitra_id' => $mitraId,
            ], [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'last_message_at' => now(),
            ]);
            
            $mitra = Mitra::findOrFail($mitraId);
            $viewerType = 'user';
        }

        $messages = $thread->messages()->orderBy('created_at', 'asc')->get();

        if (request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest' || request()->expectsJson()) {
            return response()->json([
                'messages' => $messages->map(function ($msg) use ($viewerType, $user) {
                    $isMine = false;
                    if ($viewerType === 'mitra') {
                        $isMine = $msg->sender_type === 'mitra' && (string) $msg->sender_id === (string) $user->mitra_id;
                    } else {
                        $isMine = $msg->sender_type === 'user' && (string) $msg->sender_id === (string) $user->id;
                    }
                    return [
                        'id'          => $msg->id,
                        'message'     => $msg->message,
                        'sender_type' => $msg->sender_type,
                        'time'        => $msg->created_at->format('H:i'),
                    ];
                })
            ]);
        }

        return view('pages.user.chat', [
            'threads' => $threads,
            'mitraId' => $mitraId,
            'mitra' => $mitra,
            'messages' => $messages,
            'viewerType' => $viewerType,
            'reportContext' => $thread->report,
            'thread' => $thread,
        ]);
    }

    public function send(Request $request, string $mitraId)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $user = auth()->user();
        $isMitra = $user && $user->role === 'mitra';

        if ($isMitra) {
            $thread = ChatThread::where('user_id', $mitraId)
                ->where('mitra_id', $user->mitra_id)
                ->firstOrFail();
            $senderType = 'mitra';
            $senderId = $user->mitra_id;
        } else {
            $thread = ChatThread::where('user_id', $user->id)
                ->where('mitra_id', $mitraId)
                ->firstOrFail();
            $senderType = 'user';
            $senderId = $user->id;
        }

        $msg = ChatMessage::create([
            'chat_thread_id' => $thread->id,
            'sender_type'    => $senderType,
            'sender_id'      => $senderId,
            'message'        => $request->message,
        ]);

        $thread->update(['last_message_at' => now()]);

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }
}
