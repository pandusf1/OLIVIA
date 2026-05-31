<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\ChatThread;
use App\Models\Partner;
use Illuminate\Http\Request;
use App\Services\FonnteService;

class TrackingController extends Controller
{
    public function show($id)
    {
        $report = Report::with([
            'evidences',
            'statusLogs',
            'partner',
            'assignedPartner',
            'handlerUser',
            'partnerRoutings.partner',
            'timelineEvents',
            'chronologies',
        ])->findOrFail($id);

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
        $report = Report::with([
            'evidences',
            'assignedPartner',
            'handlerUser',
            'partnerRoutings.partner',
            'timelineEvents',
        ])->findOrFail($id);

        return response()->json($this->buildLivePayload($report));
    }

    public function updateLocation(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Allow update if auth user is the creator or session has the report id
        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || in_array($report->id, $request->session()->get('my_reports', []));

        if (!$isCreator) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $report->latitude = $request->input('latitude');
        $report->longitude = $request->input('longitude');
        $report->save();

        return response()->json(['ok' => true]);
    }

    public function resolve(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Allow resolve if auth user is the creator or session has the report id
        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || in_array($report->id, $request->session()->get('my_reports', []));

        if (!$isCreator) {
            return redirect()->back()->with('error', 'Anda tidak berhak menyelesaikan laporan ini.');
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
                $report = Report::where('id', $searchId)
                    ->orWhere('id', 'like', $searchId . '%')
                    ->first();
                
                if ($report) {
                    return redirect('/tracking/' . $report->id);
                }
            }
            return back()->with('error', 'Laporan dengan ID tersebut tidak ditemukan.');
        }
        return view('pages.tracking_search');
    }

    private function buildLivePayload(Report $report): array
    {
        $assignedPartner = $report->assignedPartner;
        $relevantRoutings = $this->relevantPartnerRoutings($report);
        $pendingRoutings = $relevantRoutings->where('status', 'pending');
        $pendingCount = $pendingRoutings->count();
        $reviewingCount = $pendingRoutings
            ->where('status', 'pending')
            ->filter(fn ($routing) => filled($routing->reviewed_at))
            ->count();

        $humanMessage = $this->humanStatusMessage($report, $pendingCount, $reviewingCount);
        $eta = $assignedPartner
            ? 'Partner sudah terhubung'
            : ($pendingRoutings->min('estimated_response_minutes')
                ? $pendingRoutings->min('estimated_response_minutes') . '-' . ($pendingRoutings->min('estimated_response_minutes') + 3) . ' menit'
                : '3-5 menit');

        $latestMessages = collect();
        if ($assignedPartner && $report->user_id) {
            $thread = ChatThread::query()
                ->where('user_id', $report->user_id)
                ->where('partner_id', $assignedPartner->id)
                ->first();

            $latestMessages = $thread
                ? $thread->messages()->latest()->limit(3)->get()->reverse()->values()
                : collect();
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
            'escalation_message' => $report->escalated_at
                ? 'Admin Safora sudah diberi peringatan karena belum ada partner yang menerima dalam batas awal.'
                : 'Jika belum ada partner menerima dalam 3 menit, sistem akan mencoba ulang dan memberi peringatan ke admin.',
            'assigned_partner' => $assignedPartner ? [
                'id' => $assignedPartner->id,
                'name' => $assignedPartner->partner_name,
                'specialization' => $this->partnerTypeLabel($assignedPartner->partner_type),
                'city' => $assignedPartner->city,
                'verified' => (bool) $assignedPartner->verified,
                'handler_name' => $report->handlerUser?->name,
                'assigned_at' => optional($report->assigned_at)->format('d M Y, H:i'),
            ] : null,
            'routed_partners' => $relevantRoutings
                ->sortByDesc(fn ($routing) => $routing->status === 'accepted')
                ->values()
                ->map(fn ($routing) => [
                    'name' => $routing->partner?->partner_name ?? 'Partner Safora',
                    'specialization' => $this->partnerTypeLabel($routing->partner?->partner_type),
                    'city' => $routing->partner?->city,
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
        ];
    }

    private function humanStatusTitle(Report $report): string
    {
        return match ($report->status) {
            'Submitted' => 'Laporan diterima',
            'Routed' => 'Mencari partner terdekat',
            'Viewed' => 'Partner sedang meninjau',
            'Assigned' => 'Partner sudah menerima kasus',
            'In Progress' => 'Kasus sedang ditangani',
            'Resolved' => 'Kasus selesai',
            default => 'Safora sedang memproses laporan',
        };
    }

    private function humanStatusMessage(Report $report, int $pendingCount, int $reviewingCount): string
    {
        if ($report->handler_partner_id && $report->assignedPartner) {
            return 'Laporan Anda sekarang ditangani oleh ' . $report->assignedPartner->partner_name . '. Chat krisis sudah terbuka untuk koordinasi.';
        }

        if ($reviewingCount > 0) {
            return $reviewingCount . ' institusi sedang meninjau laporan Anda. Kami akan langsung memberi kabar saat ada yang menerima.';
        }

        if ($pendingCount > 0) {
            return 'Laporan Anda sudah diteruskan ke ' . $pendingCount . ' institusi terdekat yang sesuai kategori. Kami sedang menunggu partner tersedia menerima kasus ini.';
        }

        return 'Kami masih mencoba menghubungkan Anda dengan responder. Jika kondisi memburuk, hubungi 112 sekarang.';
    }

    private function nextInstruction(Report $report): string
    {
        if ($report->status === 'Resolved') {
            return 'Simpan kode laporan ini jika Anda perlu menambah bukti atau tindak lanjut.';
        }

        if ($report->handler_partner_id) {
            return 'Buka chat dan kirim pesan sesingkat mungkin: lokasi detail, kondisi Anda, atau bantuan yang dibutuhkan.';
        }

        return 'Tetap di tempat aman jika memungkinkan. Jangan menunggu Safora jika nyawa terancam, hubungi 112 segera.';
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

    private function partnerTypeLabel(?string $type): string
    {
        return match ($type) {
            'ambulance' => 'Medis Darurat',
            'legal' => 'Bantuan Hukum',
            'counselor' => 'Psikososial',
            'pemadam' => 'Pemadam / Rescue',
            default => 'Partner Krisis',
        };
    }

    private function relevantPartnerRoutings(Report $report)
    {
        $partnerTypes = Partner::partnerTypesForCategory($report->category);

        if (empty($partnerTypes)) {
            return collect();
        }

        return $report->partnerRoutings
            ->filter(fn ($routing) => $routing->partner && Partner::matchesCategory($routing->partner->partner_type, $report->category))
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
            || in_array($report->id, $request->session()->get('my_reports', []));

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

        if (!$isCreator && !$isTrustedContact && !$isWitnessWithin5Km) {
            return response()->json(['error' => 'Akses ditolak. Hanya korban, saksi (< 5 km), atau kontak terpercaya yang bisa menambah kronologi.'], 403);
        }

        $request->validate([
            'description' => 'required|string',
        ]);

        $role = 'Saksi';
        $writerName = 'anonymous';

        if ($isCreator) {
            $role = 'Korban';
            $writerName = auth()->check() ? auth()->user()->name : 'anonymous';
        } elseif ($isTrustedContact) {
            $role = 'Kontak Terpercaya';
            $writerName = auth()->user()->name;
        } else {
            $role = 'Saksi';
            $writerName = auth()->check() ? auth()->user()->name : 'anonymous';
        }

        $chronology = \App\Models\ReportChronology::create([
            'report_id' => $report->id,
            'user_id' => auth()->id(),
            'writer_name' => $writerName,
            'role' => $role,
            'description' => $request->description,
        ]);

        return response()->json([
            'ok' => true,
            'chronology' => [
                'id' => $chronology->id,
                'writer_name' => $chronology->role === 'Korban' ? "Korban ({$chronology->writer_name})" : ($chronology->role === 'Saksi' ? "Saksi ({$chronology->writer_name})" : "{$chronology->role} ({$chronology->writer_name})"),
                'description' => $chronology->description,
                'created_at' => $chronology->created_at->format('d M Y, H:i'),
            ]
        ]);
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
