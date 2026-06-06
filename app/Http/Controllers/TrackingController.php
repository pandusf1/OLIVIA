<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ChatThread;
use App\Models\Mitra;
use Illuminate\Http\Request;
use App\Services\FonnteService;

class TrackingController extends Controller
{
    public function resolveReport($id, array $relations = [])
    {
        // Auto-sync cookie 'safora_my_reports' to session to avoid client-side reload loops
        try {
            $sessionReports = session()->get('my_reports', []);
            $cookieReports = request()->hasCookie('safora_my_reports') 
                ? json_decode(request()->cookie('safora_my_reports'), true) ?: []
                : [];
            
            $newReports = array_unique(array_merge($sessionReports, $cookieReports));
        } catch (\Exception $e) {
            $newReports = [];
        }

        $id = trim($id);
        $id = ltrim($id, '#');
        $id = trim($id);

        $report = null;

        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
            $report = Report::where('id', $id)->first();
        }

        if (!$report) {
            $report = Report::whereRaw("CAST(id AS text) LIKE ?", [strtolower($id) . '%'])->first();
        }

        if (!$report) {
            abort(404, 'Laporan tidak ditemukan.');
        }

        if (!empty($relations)) {
            $report->load($relations);
        }

        // Add report to session/cookie if it is anonymous (user_id is null)
        if ($report->user_id === null) {
            if (!in_array($report->id, $newReports)) {
                $newReports[] = $report->id;
            }
        }

        // Filter out report IDs belonging to other users
        if (!empty($newReports)) {
            $newReports = Report::whereIn('id', $newReports)
                ->where(function ($query) {
                    $query->whereNull('user_id');
                    if (auth()->check()) {
                        $query->orWhere('user_id', auth()->id());
                    }
                })
                ->pluck('id')
                ->toArray();
        }

        try {
            session()->put('my_reports', $newReports);
            cookie()->queue(cookie('safora_my_reports', json_encode($newReports), 60 * 24 * 30));
        } catch (\Exception $e) {
            // ignore session/cookie issues
        }

        return $report;
    }

    public function show($id)
    {
        $report = $this->resolveReport($id, [
            'evidences',
            'statusLogs',
            'mitra',
            'assignedMitra',
            'handlerUser',
            'mitraRoutings.mitra',
            'timelineEvents',
            'chronologies',
        ]);

        $isTrustedContact = false;
        if (auth()->check() && $report->user_id) {
            $userPhone = auth()->user()->phone;
            if ($userPhone) {
                $isTrustedContact = \App\Models\TrustedContact::where('user_id', $report->user_id)
                    ->where('contact_phone', $userPhone)
                    ->where('is_verified', true)
                    ->exists();
            }
        }

        return view('pages.tracking', [
            'report' => $report,
            'livePayload' => $this->buildLivePayload($report),
            'isTrustedContact' => $isTrustedContact,
        ]);
    }

    public function live($id)
    {
        $report = $this->resolveReport($id, [
            'evidences',
            'assignedMitra',
            'handlerUser',
            'mitraRoutings.mitra',
            'timelineEvents',
            'chronologies',
        ]);

        return response()->json($this->buildLivePayload($report));
    }

    public function updateLocation(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $user = auth()->user();
        $isMitra = $user && $user->role === 'mitra';

        // Allow update if auth user is the creator or session has the report id (and is not a mitra responder)
        $isCreator = !$isMitra && (
            (auth()->check() && auth()->id() === $report->user_id) 
            || ($report->user_id === null && in_array($report->id, $request->session()->get('my_reports', [])))
        );

        if (!$isCreator) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $wasNull = ($report->latitude === null || $report->longitude === null);

        $report->latitude = $request->input('latitude');
        $report->longitude = $request->input('longitude');
        
        if ($wasNull) {
            $report->location_verified_at = now();
        }
        
        $report->save();

        if ($wasNull) {
            // Log timeline event
            $report->timelineEvents()->create([
                'report_id' => $report->id,
                'event_type' => 'gps_verified',
                'event_message' => 'Lokasi GPS berhasil diterima sehingga mitra dapat melihat area kejadian lebih cepat.',
                'actor_type' => 'system',
            ]);

            $trackingLink = url('/tracking/' . $report->id);
            $mapsLink = "https://maps.google.com/?q={$report->latitude},{$report->longitude}";

            // Recalculate distance for existing routed mitras
            foreach ($report->mitraRoutings as $routing) {
                if ($routing->mitra) {
                    $dist = Mitra::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $routing->mitra->latitude, (float) $routing->mitra->longitude);
                    $routing->update(['distance_km' => $dist]);
                }
            }

            // Route to nearest mitras if none exists and not unknown_emergency
            $mitras = $report->routingMitras;
            if ($mitras->isEmpty() && strtolower((string) $report->category) !== 'unknown_emergency') {
                $mitras = Mitra::routeMultipleByCategory(
                    $report->category,
                    5,
                    (float) $report->latitude,
                    (float) $report->longitude
                );
                
                // Filter within 20 km
                $mitras = $mitras->filter(function($p) use ($report) {
                    $dist = Mitra::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $p->latitude, (float) $p->longitude);
                    return $dist <= 20.0;
                });

                if ($mitras->isNotEmpty()) {
                    $report->update([
                        'status' => 'Routed',
                        'routed_mitra_id' => $mitras->first()->id,
                    ]);

                    foreach ($mitras as $mitra) {
                        \App\Models\ReportMitraRouting::create([
                            'report_id' => $report->id,
                            'mitra_id' => $mitra->id,
                            'status' => 'pending',
                            'routed_at' => now(),
                            'distance_km' => Mitra::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $mitra->latitude, (float) $mitra->longitude),
                            'estimated_response_minutes' => $mitra->mitra_type === 'ambulance' ? 5 : 8,
                        ]);
                    }
                }
            }

            // Alert via WhatsApp torouted mitras
            foreach ($mitras as $mitra) {
                if ($mitra->phone) {
                    $mitraMessage =
                        "🚨 *Safora - Laporan Darurat Baru (GPS Terdeteksi)*\n\n" .
                        "Kategori: *{$report->category}*\n\n" .
                        "📍 Lokasi:\n{$mapsLink}\n\n" .
                        "🔗 Tracking:\n{$trackingLink}\n\n" .
                        "Buka dashboard Safora untuk menerima laporan ini.";
                    try {
                        FonnteService::send($mitra->phone, $mitraMessage);
                    } catch (\Exception $e) {}
                }
            }

            // Alert to admin / test phone
            if (env('ADMIN_PHONE')) {
                $testMsg = "🚨 *Safora - Laporan Darurat Baru (GPS Updated)*\n\nKategori: *{$report->category}*\n📍 Lokasi:\n{$mapsLink}\n🔗 Tracking:\n{$trackingLink}";
                try { FonnteService::send(env('ADMIN_PHONE'), $testMsg); } catch(\Exception $e){}
            }

            // Alert to nearest users within 10 km
            $this->notifyNearestUsersFromTracking($report, $trackingLink, $mapsLink, 3);
        }

        return response()->json(['ok' => true]);
    }

    private function notifyNearestUsersFromTracking(Report $report, string $trackingLink, string $mapsLink, int $limit = 3): void
    {
        $fromUserId = $report->user_id;

        $message =
            "🚨 *ALERT DARURAT — Safora*\n\n" .
            "Ada korban meminta pertolongan!\n\n" .
            "Kategori: *{$report->category}*\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Pantau status:\n{$trackingLink}\n\n" .
            "_Pesan ini dikirim otomatis oleh Safora._";

        $targetsQuery = \App\Models\UserLocation::query()
            ->whereHas('user', function ($q) {
                $q->where('role', 'user')->where('receive_nearby_alerts', true);
            });
            
        if ($fromUserId) {
            $targetsQuery->where('user_id', '!=', $fromUserId);
        }
            
        $targets = $targetsQuery->get();
        if ($targets->isEmpty()) {
            return;
        }

        $lat = (float) $report->latitude;
        $lng = (float) $report->longitude;

        $targetsWithDistance = $targets
            ->map(function ($loc) use ($lat, $lng) {
                $distanceKm = $this->haversineKm($lat, $lng, (float) $loc->latitude, (float) $loc->longitude);
                return [
                    'user_id' => $loc->user_id,
                    'distance_km' => $distanceKm,
                ];
            })
            ->filter(function ($t) {
                return $t['distance_km'] <= 10.0;
            })
            ->sortBy('distance_km')
            ->take($limit)
            ->values();

        foreach ($targetsWithDistance as $t) {
            $user = \App\Models\User::query()->where('id', $t['user_id'])->where('role', 'user')->first();
            if (!$user || !$user->phone) {
                continue;
            }

            try {
                FonnteService::send($user->phone, $message);
                
                $user->nearby_alert_count += 1;
                if ($user->nearby_alert_count >= $user->next_nearby_alert_threshold) {
                    $settingUrl = url('/settings');
                    $noticeMsg = "ℹ️ *Info Safora*\nAnda telah menerima beberapa alert korban terdekat. Jika Anda merasa terganggu, Anda bisa menonaktifkan fitur ini di pengaturan aplikasi.\n\nAtur di sini: {$settingUrl}";
                    FonnteService::send($user->phone, $noticeMsg);
                    
                    $user->nearby_alert_count = 0;
                    $user->next_nearby_alert_threshold += 3;
                }
                $user->save();
            } catch (\Throwable $e) {
                \Log::error('notifyNearestUsersFromTracking Fonnte send failed', [
                    'target_user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            \App\Models\ReportUserRouting::create([
                'report_id' => $report->id,
                'target_user_id' => $user->id,
                'routed_at' => now(),
            ]);
        }
    }

    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Allow resolve if auth user is the creator, session has the report id, or user is a verified trusted contact
        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || ($report->user_id === null && in_array($report->id, $request->session()->get('my_reports', [])));

        $isTrustedContact = false;
        if (auth()->check() && $report->user_id) {
            $userPhone = auth()->user()->phone;
            if ($userPhone) {
                $isTrustedContact = \App\Models\TrustedContact::where('user_id', $report->user_id)
                    ->where('contact_phone', $userPhone)
                    ->where('is_verified', true)
                    ->exists();
            }
        }

        if (!$isCreator && !$isTrustedContact) {
            return redirect()->back()->with('error', 'Anda tidak berhak menyelesaikan laporan ini.');
        }

        // Only allow resolving if currently handled by a mitra (In Progress or Assigned)
        if (!in_array($report->status, ['In Progress', 'Assigned'])) {
            return redirect()->back()->with('error', 'Laporan hanya bisa diselesaikan jika sedang ditangani oleh mitra.');
        }

        if ($report->status !== 'Resolved') {
            $report->update(['status' => 'Resolved']);
            
            $report->timelineEvents()->create([
                'event_type' => 'resolved',
                'event_message' => 'Laporan telah ditandai selesai oleh pelapor.',
            ]);

            // Notify trusted contacts
            if ($report->user && $report->user->trustedContacts()->where('is_verified', true)->count() > 0) {
                foreach ($report->user->trustedContacts()->where('is_verified', true)->get() as $contact) {
                    $message = "Safora Info:\n\n" .
                        "Laporan darurat yang dibuat oleh *{$report->user->name}* telah ditandai SELESAI dan pelapor menyatakan bahwa dirinya AMAN.\n\n" .
                        "Kategori: *{$report->category}*\n" .
                        "Tracking: " . url('/tracking/' . $report->id);
                        
                    FonnteService::send($contact->contact_phone, $message);
                }
            }
            
            return redirect('/tracking/' . $report->id)->with('success', 'Laporan berhasil diselesaikan dan kontak terpercaya telah dihubungi.');
        }

        return redirect('/tracking/' . $report->id);
    }

    public function search(Request $request)
    {
        if ($request->has('id') && $request->id) {
            $searchId = trim($request->id);
            $searchId = ltrim($searchId, '#');
            $searchId = trim($searchId);

            if ($searchId) {
                $searchId = strtolower($searchId);
                
                // If it is a full valid UUID, query directly. Otherwise cast and match prefix (PostgreSQL compatible)
                if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $searchId)) {
                    $report = Report::where('id', $searchId)->first();
                } else {
                    $report = Report::whereRaw("CAST(id AS text) LIKE ?", [$searchId . '%'])->first();
                }
                
                if ($report) {
                    return redirect('/tracking/' . $report->id);
                }
            }
            return back()->with('error', 'Laporan dengan ID tersebut tidak ditemukan.');
        }
        return view('pages.tracking_search');
    }

    public function reAlert(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Perbolehkan re-alert jika pengguna login adalah pembuat atau report ID ada di session my_reports
        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || ($report->user_id === null && in_array($report->id, $request->session()->get('my_reports', [])));

        if (!$isCreator) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $retryCountKey = "report_{$report->id}_retry_count";
        $retryCount = (int) \Illuminate\Support\Facades\Cache::store('database')->get($retryCountKey, 0);

        if ($retryCount < 3) {
            return response()->json(['error' => 'Fitur alert ulang belum aktif.'], 400);
        }

        $lastManualAlertAtKey = "report_{$report->id}_last_manual_alert_at";
        $lastManualAlertAt = \Illuminate\Support\Facades\Cache::store('database')->get($lastManualAlertAtKey);

        if ($lastManualAlertAt) {
            $diff = now()->diffInSeconds(\Carbon\Carbon::parse($lastManualAlertAt));
            if ($diff < 600) {
                $cooldownSeconds = 600 - $diff;
                return response()->json([
                    'error' => "Mohon tunggu {$cooldownSeconds} detik sebelum mengirimkan alert ulang.",
                    'cooldown_seconds' => $cooldownSeconds
                ], 429);
            }
        }

        // Simpan waktu manual alert baru
        \Illuminate\Support\Facades\Cache::store('database')->put($lastManualAlertAtKey, now()->toDateTimeString(), now()->addDays(7));

        // Dapatkan semua mitra yang pending
        $pendingRoutings = $report->mitraRoutings()->where('status', 'pending')->get();

        $mapsLink = $report->latitude
            ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
            : 'Lokasi tidak tersedia';
        $trackingLink = url('/tracking/' . $report->id);

        foreach ($pendingRoutings as $routing) {
            $mitra = $routing->mitra;
            if ($mitra && $mitra->phone) {
                $mitraMessage =
                    "🚨 *ALERT PENGINGAT MANUAL DARURAT*\n\n" .
                    "Korban mengirim ulang alert manual karena belum menerima bantuan!\n" .
                    "Kategori: *{$report->category}*\n\n" .
                    "📍 Lokasi:\n{$mapsLink}\n\n" .
                    "🔗 Tracking:\n{$trackingLink}\n\n" .
                    "Mohon segera buka dashboard Safora dan terima laporan ini.";

                try {
                    FonnteService::send($mitra->phone, $mitraMessage);
                } catch (\Exception $e) {
                    \Log::error("Failed to send manual re-alert WA to mitra", [
                        'mitra_id' => $mitra->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Tambahkan event ke timeline
        $report->timelineEvents()->create([
            'event_type' => 'mitra_manual_alert',
            'event_message' => 'Pelapor mengirimkan ulang alert WhatsApp ke mitra secara manual.',
            'actor_type' => auth()->check() ? 'user' : 'system',
            'actor_id' => auth()->id(),
        ]);

        return response()->json([
            'ok' => true,
            'cooldown_seconds' => 600
        ]);
    }

    public function buildLivePayload(Report $report): array
    {
        $assignedMitra = $report->assignedMitra;
        $relevantRoutings = $this->relevantMitraRoutings($report);
        $pendingRoutings = $relevantRoutings->where('status', 'pending');
        $pendingCount = $pendingRoutings->count();
        $reviewingCount = $pendingRoutings
            ->where('status', 'pending')
            ->filter(fn ($routing) => filled($routing->reviewed_at))
            ->count();

        $humanMessage = $this->humanStatusMessage($report, $pendingCount, $reviewingCount);
        $eta = ($report->status === 'Resolved')
            ? 'Selesai'
            : ($assignedMitra
                ? 'Mitra sudah terhubung'
                : ($pendingRoutings->min('estimated_response_minutes')
                    ? $pendingRoutings->min('estimated_response_minutes') . '-' . ($pendingRoutings->min('estimated_response_minutes') + 3) . ' menit'
                    : '3-5 menit'));

        $latestMessages = collect();
        if ($assignedMitra && $report->user_id) {
            $thread = ChatThread::query()
                ->where('user_id', $report->user_id)
                ->where('mitra_id', $assignedMitra->id)
                ->first();

            $latestMessages = $thread
                ? $thread->messages()->latest()->limit(3)->get()->reverse()->values()
                : collect();
        }

        $retryCountKey = "report_{$report->id}_retry_count";
        $retryCount = (int) \Illuminate\Support\Facades\Cache::store('database')->get($retryCountKey, 0);

        $lastManualAlertAtKey = "report_{$report->id}_last_manual_alert_at";
        $lastManualAlertAt = \Illuminate\Support\Facades\Cache::store('database')->get($lastManualAlertAtKey);
        $cooldownSeconds = 0;
        if ($lastManualAlertAt) {
            $diff = now()->diffInSeconds(\Carbon\Carbon::parse($lastManualAlertAt));
            if ($diff < 600) {
                $cooldownSeconds = 600 - $diff;
            }
        }

        return [
            'report' => [
                'id' => $report->id,
                'short_id' => strtoupper(substr($report->id, 0, 8)),
                'category' => $report->category,
                'status' => $report->status,
                'urgency_level' => $report->urgency_level ?? 'high',
                'created_at' => optional($report->created_at)->format('d M Y, H:i'),
                'incident_date' => $report->incident_date ? \Carbon\Carbon::parse($report->incident_date)->format('d M Y, H:i') : null,
                'anonymous' => (bool) $report->anonymous,
                'location' => [
                    'latitude' => $report->latitude,
                    'longitude' => $report->longitude,
                    'text' => $report->location_text,
                    'verified' => filled($report->location_verified_at),
                    'maps_url' => $report->latitude && $report->longitude
                        ? 'https://www.google.com/maps?q=' . $report->latitude . ',' . $report->longitude
                        : null,
                ],
            ],
            'current_status' => $this->humanStatusTitle($report),
            'human_message' => $humanMessage,
            'eta' => $eta,
            'next_instruction' => $this->nextInstruction($report),
            'escalation_message' => $retryCount >= 3
                ? 'Sistem selesai mengirim ulang alert pengingat otomatis. Anda kini dapat mengirim ulang alert secara manual jika diperlukan.'
                : 'Jika belum ada mitra menerima dalam 5 menit, sistem akan mencoba ulang alert WhatsApp secara bertahap s.d. 3 kali.',
            'assigned_mitra' => $assignedMitra ? [
                'id' => $assignedMitra->id,
                'name' => $assignedMitra->mitra_name,
                'specialization' => $this->mitraTypeLabel($assignedMitra->mitra_type),
                'city' => $assignedMitra->city,
                'verified' => (bool) $assignedMitra->verified,
                'handler_name' => $report->handlerUser?->name,
                'assigned_at' => optional($report->assigned_at)->format('d M Y, H:i'),
                'latitude' => $assignedMitra->latitude,
                'longitude' => $assignedMitra->longitude,
            ] : null,
            'routed_mitras' => $relevantRoutings
                ->sortByDesc(fn ($routing) => $routing->status === 'accepted')
                ->values()
                ->map(fn ($routing) => [
                    'name' => $routing->mitra?->mitra_name ?? 'Mitra Safora',
                    'specialization' => $this->mitraTypeLabel($routing->mitra?->mitra_type),
                    'city' => $routing->mitra?->city,
                    'estimated_response' => $routing->estimated_response_minutes
                        ? $routing->estimated_response_minutes . '-' . ($routing->estimated_response_minutes + 3) . ' menit'
                        : '3-5 menit',
                    'distance' => $routing->distance_km !== null ? number_format($routing->distance_km, 1) . ' km' : null,
                    'status' => $this->routingDisplayStatus($routing),
                ]),
            'timeline' => $report->timelineEvents
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($event) => [
                    'type' => $event->event_type,
                    'message' => $event->event_message,
                    'time' => optional($event->created_at)->format('H:i'),
                ]),
            'latest_messages' => $latestMessages->map(fn ($message) => [
                'sender_type' => $message->sender_type,
                'message' => $message->message,
                'time' => optional($message->created_at)->format('H:i'),
            ]),
            'hotlines' => $this->hotlines(),
            'retry_count' => $retryCount,
            'cooldown_seconds' => $cooldownSeconds,
            'chronologies' => $report->chronologies->sortBy('created_at')->values()->map(function ($chrono) {
                return [
                    'id' => $chrono->id,
                    'role' => $chrono->role,
                    'writer_name' => $chrono->writer_name,
                    'description' => $chrono->description,
                    'created_at' => $chrono->created_at->format('d M Y, H:i'),
                ];
            })->all(),
            'evidences' => $report->evidences->sortBy('uploaded_at')->values()->map(function ($ev) {
                return [
                    'id' => $ev->id,
                    'file_url' => url('/evidences/view/' . $ev->id),
                    'file_type' => $ev->file_type,
                    'file_hash' => $ev->file_hash,
                    'uploaded_at' => $ev->uploaded_at ? ($ev->uploaded_at instanceof \Carbon\Carbon ? $ev->uploaded_at->format('d M Y, H:i') : \Carbon\Carbon::parse($ev->uploaded_at)->format('d M Y, H:i')) : null,
                    'uploader_role' => $ev->uploader_role ?? 'Saksi',
                ];
            })->all(),
            'can_view_evidence' => (bool) ($report->show_evidence 
                || (auth()->check() && (auth()->id() === $report->user_id || auth()->user()->role === 'mitra')) 
                || ($report->user_id === null && in_array($report->id, session()->get('my_reports', [])))),
        ];
    }

    private function humanStatusTitle(Report $report): string
    {
        return match ($report->status) {
            'Submitted' => 'Diajukan',
            'Routed' => 'Diteruskan',
            'Viewed' => 'Ditinjau',
            'Assigned' => 'Diterima',
            'In Progress' => 'Diproses',
            'Resolved' => 'Selesai',
            default => 'Diajukan',
        };
    }

    private function humanStatusMessage(Report $report, int $pendingCount, int $reviewingCount): string
    {
        if ($report->status === 'Resolved') {
            if ($report->handler_mitra_id && $report->assignedMitra) {
                return 'Kasus ini telah selesai ditangani oleh ' . $report->assignedMitra->mitra_name . '. Terima kasih atas kerja sama Anda.';
            }
            return 'Kasus ini telah selesai ditangani. Terima kasih atas kerja sama Anda.';
        }

        if ($report->handler_mitra_id && $report->assignedMitra) {
            return 'Laporan Anda sekarang ditangani oleh ' . $report->assignedMitra->mitra_name . '. Chat krisis sudah terbuka untuk koordinasi.';
        }

        if ($reviewingCount > 0) {
            return $reviewingCount . ' institusi sedang meninjau laporan Anda. Kami akan langsung memberi kabar saat ada yang menerima.';
        }

        if ($pendingCount > 0) {
            return 'Laporan Anda sudah diteruskan ke ' . $pendingCount . ' institusi terdekat yang sesuai kategori. Kami sedang menunggu mitra tersedia menerima kasus ini.';
        }

        return 'Kami masih mencoba menghubungkan Anda dengan responder. Jika kondisi memburuk, hubungi 112 sekarang.';
    }

    private function nextInstruction(Report $report): string
    {
        if ($report->status === 'Resolved') {
            return 'Simpan kode laporan ini jika Anda perlu menambah bukti atau tindak lanjut.';
        }

        if ($report->handler_mitra_id) {
            return 'Buka chat dan kirim pesan sesingkat mungkin: lokasi detail, kondisi Anda, atau bantuan yang dibutuhkan.';
        }

        return 'Tetap di tempat aman jika memungkinkan. Jangan menunggu mitra jika nyawa terancam, hubungi 112 segera.';
    }

    private function routingDisplayStatus($routing): string
    {
        if ($routing->status === 'accepted') {
            return 'accepted';
        }

        if ($routing->status === 'expired') {
            return 'unavailable';
        }

        return filled($routing->reviewed_at) ? 'reviewing' : 'waiting';
    }

    private function mitraTypeLabel(?string $type): string
    {
        return match ($type) {
            'ambulance' => 'Medis Darurat',
            'legal' => 'Bantuan Hukum',
            'counselor' => 'Psikososial',
            'pemadam' => 'Pemadam / Rescue',
            default => 'Mitra Krisis',
        };
    }

    private function relevantMitraRoutings(Report $report)
    {
        $mitraTypes = Mitra::mitraTypesForCategory($report->category);

        if (empty($mitraTypes)) {
            return collect();
        }

        return $report->mitraRoutings
            ->filter(fn ($routing) => $routing->mitra && Mitra::matchesCategory($routing->mitra->mitra_type, $report->category))
            ->values();
    }

    private function hotlines(): array
    {
        return [
            ['label' => 'Darurat Nasional', 'phone' => '112'],
            ['label' => 'Ambulans / Kesehatan', 'phone' => '119'],
            ['label' => 'Polisi', 'phone' => '110'],
            ['label' => 'SAPA / KDRT', 'phone' => '129'],
            ['label' => 'KPAI', 'phone' => '02131901556'],
        ];
    }

    public function storeChronology(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);

        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || ($report->user_id === null && in_array($report->id, $request->session()->get('my_reports', [])));

        $isTrustedContact = false;
        if (auth()->check() && $report->user_id) {
            $userPhone = auth()->user()->phone;
            if ($userPhone) {
                $isTrustedContact = \App\Models\TrustedContact::where('user_id', $report->user_id)
                    ->where('contact_phone', $userPhone)
                    ->where('is_verified', true)
                    ->exists();
            }
        }

        $isWitnessWithin5Km = false;
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        if ($lat !== null && $lng !== null && $report->latitude && $report->longitude) {
            $dist = $this->haversineKm((float)$lat, (float)$lng, (float)$report->latitude, (float)$report->longitude);
            if ($dist <= 5.0) {
                $isWitnessWithin5Km = true;
            }
        }

        $isMitra = auth()->check() && auth()->user()->role === 'mitra';
        if ($isMitra) {
            $mitraId = auth()->user()->mitra_id;
            if ($report->handler_mitra_id !== $mitraId) {
                return response()->json(['error' => 'Akses ditolak. Kasus ini ditangani oleh mitra lain.'], 403);
            }
        }

        if (!$isCreator && !$isTrustedContact && !$isWitnessWithin5Km && !$isMitra) {
            return response()->json(['error' => 'Akses ditolak. Hanya korban, saksi (< 5 km), mitra krisis, atau kontak terpercaya yang bisa menambah kronologi.'], 403);
        }

        $request->validate([
            'description' => 'required|string',
        ]);

        $role = 'Saksi';
        $writerName = 'anonim';

        if (auth()->check() && auth()->user()->role === 'mitra') {
            $role = 'Mitra';
            $mitra = \App\Models\Mitra::find(auth()->user()->mitra_id);
            $writerName = $mitra ? $mitra->mitra_name : auth()->user()->name;
        } elseif ($isCreator) {
            $role = 'Korban';
            $writerName = auth()->check() ? auth()->user()->name : 'anonim';
        } elseif ($isTrustedContact) {
            $role = 'Kontak Terpercaya';
            $writerName = auth()->user()->name;
        } else {
            $role = 'Saksi';
            $writerName = auth()->check() ? auth()->user()->name : 'anonim';
        }

        $chronology = \App\Models\ReportChronology::create([
            'report_id' => $report->id,
            'user_id' => auth()->id(),
            'writer_name' => $writerName,
            'role' => $role,
            'description' => $request->description,
        ]);

        $writerLabel = $chronology->role === 'Korban'
            ? "Korban ({$chronology->writer_name})"
            : ($chronology->role === 'Saksi'
                ? "Saksi ({$chronology->writer_name})"
                : "{$chronology->role} ({$chronology->writer_name})");

        return response()->json([
            'ok' => true,
            'chronology' => [
                'id' => $chronology->id,
                'writer_name' => $writerLabel,
                'description' => $chronology->description,
                'created_at' => $chronology->created_at->format('d M Y, H:i'),
            ]
        ]);
    }

    public function updateStatusByVictim(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || ($report->user_id === null && in_array($report->id, $request->session()->get('my_reports', [])));

        if (!$isCreator) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:In Progress,Resolved',
        ]);

        $oldStatus = $report->status;
        $report->status = $request->input('status');
        $report->last_activity_at = now();
        $report->save();

        \App\Models\ReportStatusLog::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => $request->input('status'),
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        $report->timelineEvents()->create([
            'event_type' => 'status_updated_by_victim',
            'event_message' => 'Status laporan diperbarui oleh pelapor menjadi ' . ($request->input('status') === 'Resolved' ? 'Selesai' : 'Diproses') . '.',
            'actor_type' => 'user',
            'actor_id' => auth()->id(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateMitraLocation(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        $user = auth()->user();
        $isHandlerMitra = $user && $user->role === 'mitra' && $report->handler_mitra_id === $user->mitra_id;

        if (!$isHandlerMitra) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $mitra = $report->assignedMitra;
        if ($mitra) {
            $mitra->latitude = $request->input('latitude');
            $mitra->longitude = $request->input('longitude');
            $mitra->save();
        }

        return response()->json(['ok' => true]);
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
