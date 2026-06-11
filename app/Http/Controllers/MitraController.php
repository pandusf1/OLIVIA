<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Mitra;
use App\Models\ReportMitraRouting;
use App\Models\ReportTimelineEvent;
use App\Services\FonnteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $mitraId = auth()->user()->mitra_id;

        $mitra = Mitra::findOrFail($mitraId);

        $pendingRoutings = ReportMitraRouting::query()
            ->with(['report' => fn ($query) => $query->with(['user'])->withCount('evidences')])
            ->where('mitra_id', $mitraId)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('routed_at')
            ->get();

        $activeReports = Report::query()
            ->with(['user', 'mitraRoutings' => fn ($query) => $query->where('mitra_id', $mitraId)])
            ->withCount('evidences')
            ->where('status', 'In Progress')
            ->whereHas('mitraRoutings', function ($query) use ($mitraId) {
                $query->where('mitra_id', $mitraId)->where('status', 'accepted');
            })
            ->latest('updated_at')
            ->get();

        $resolvedReports = Report::query()
            ->with(['user', 'mitraRoutings' => fn ($query) => $query->where('mitra_id', $mitraId)])
            ->withCount('evidences')
            ->where('status', 'Resolved')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->whereHas('mitraRoutings', function ($query) use ($mitraId) {
                $query->where('mitra_id', $mitraId)->where('status', 'accepted');
            })
            ->latest('updated_at')
            ->get();

        $stats = [
            'pending' => $pendingRoutings->count(),
            'progress' => $activeReports->count(),
            'resolved_month' => $resolvedReports->count(),
        ];

        // For "Semua Laporan" Tab
        $allReportsQuery = Report::whereHas('mitraRoutings', function ($q) use ($mitraId) {
            $q->where('mitra_id', $mitraId);
        });

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $allReportsQuery->where(function($q) use ($search) {
                $q->where('anonymous', false)
                  ->whereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhere(function($aq) use ($search) {
                      $aq->where('anonymous', true)
                         ->whereRaw("LOWER(?) LIKE '%anonim%'", [$search]);
                  });
            });
        }

        // Handled Filter
        if ($request->filled('handled')) {
            if ($request->handled == 'yes') {
                $allReportsQuery->where('handler_mitra_id', $mitraId);
            } elseif ($request->handled == 'no') {
                $allReportsQuery->where(function($q) use ($mitraId) {
                    $q->whereNull('handler_mitra_id')
                      ->orWhere('handler_mitra_id', '!=', $mitraId);
                });
            }
        }

        // Month & Year Filter
        if ($request->filled('month')) {
            $allReportsQuery->whereMonth('created_at', $request->month);
        }
        if ($request->filled('year')) {
            $allReportsQuery->whereYear('created_at', $request->year);
        }

        $activeClients = collect();
        if (in_array($mitra->mitra_type, ['legal', 'counselor'], true)) {
            $activeClients = \App\Models\UserMitraPayment::query()
                ->with(['user', 'priceList'])
                ->where('mitra_id', $mitraId)
                ->latest('paid_at')
                ->get()
                ->groupBy('user_id');
        }

        $allReports = $allReportsQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('pages.mitra.index', compact('mitra', 'pendingRoutings', 'activeReports', 'resolvedReports', 'stats', 'allReports', 'activeClients'));
    }

    public function show($id)
    {
        $idClean = trim($id);
        $idClean = ltrim($idClean, '#');
        $idClean = trim($idClean);

        $report = null;
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $idClean)) {
            $report = Report::where('id', $idClean)->first();
        }
        if (!$report) {
            $report = Report::whereRaw("CAST(id AS text) LIKE ?", [strtolower($idClean) . '%'])->first();
        }

        if (!$report) {
            abort(404, 'Laporan tidak ditemukan.');
        }

        $report->load([
            'evidences',
            'statusLogs',
        ]);

        $mitraId = auth()->user()->mitra_id;
        $routing = $report->mitraRoutings()
            ->where('mitra_id', $mitraId)
            ->first();

        if ($report->routed_mitra_id !== $mitraId && !$routing) {
            abort(403, 'Laporan ini bukan untuk mitra anda.');
        }

        $isHandling = ($report->handler_mitra_id === $mitraId);
        $isPending = ($routing && $routing->status === 'pending' && (is_null($routing->expires_at) || $routing->expires_at > now()));
        $canViewSensitive = $isHandling || $isPending;

        // Catat bahwa mitra telah melihat laporan
        if ($report->status === 'Routed' && $isPending) {
            $routing->update([
                'reviewed_at' => now(),
            ]);

            ReportTimelineEvent::create([
                'report_id' => $report->id,
                'event_type' => 'mitra_reviewing',
                'event_message' => 'Salah satu mitra sedang meninjau laporan Anda.',
                'actor_type' => 'mitra',
                'actor_id' => $mitraId,
            ]);

            $old = $report->status;
            $report->status = 'Viewed';
            $report->last_activity_at = now();
            $report->save();

            ReportStatusLog::create([
                'report_id'  => $report->id,
                'old_status' => $old,
                'new_status' => 'Viewed',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            AuditLog::log('view_report', 'report', $report->id);
        }

        $trackingController = app(\App\Http\Controllers\TrackingController::class);
        $reportLoaded = $trackingController->resolveReport($report->id, [
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
        if (auth()->check() && $reportLoaded->user_id) {
            $userPhone = auth()->user()->phone;
            if ($userPhone) {
                $isTrustedContact = \App\Models\TrustedContact::where('user_id', $reportLoaded->user_id)
                    ->where('contact_phone', $userPhone)
                    ->where('is_verified', true)
                    ->exists();
            }
        }

        return view('pages.tracking', [
            'report' => $reportLoaded,
            'livePayload' => $trackingController->buildLivePayload($reportLoaded),
            'isTrustedContact' => $isTrustedContact,
            'backUrl' => route('mitra.index'),
            'backLabel' => 'Kembali',
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string|in:Submitted,Routed,Viewed,Assigned,In Progress,Resolved,Follow-up Monitoring']);

        $report = Report::findOrFail($id);
        $mitraId = auth()->user()->mitra_id;
        $hasAcceptedRouting = $report->mitraRoutings()
            ->where('mitra_id', $mitraId)
            ->where('status', 'accepted')
            ->exists();

        if ($report->routed_mitra_id !== $mitraId && !$hasAcceptedRouting) {
            abort(403, 'Anda tidak punya akses.');
        }
        $oldStatus = $report->status;

        $report->status = $request->status;
        $report->last_activity_at = now();
        $report->save();

        ReportStatusLog::create([
            'report_id'  => $report->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        AuditLog::log('update_status', 'report', $report->id);

        ReportTimelineEvent::create([
            'report_id' => $report->id,
            'event_type' => 'status_updated',
            'event_message' => $request->status === 'Resolved'
                ? 'Kasus ditandai selesai. Safora tetap menyimpan riwayat dan bukti laporan Anda.'
                : 'Status laporan diperbarui menjadi ' . $request->status . '.',
            'actor_type' => 'mitra',
            'actor_id' => $mitraId,
        ]);

        if ($report->user && $report->user->phone) {
            $statusLabel = match ($request->status) {
                'Submitted' => 'Diajukan',
                'Routed' => 'Diteruskan',
                'Viewed' => 'Ditinjau',
                'Assigned' => 'Diterima',
                'In Progress' => 'Diproses',
                'Resolved' => 'Selesai',
                'Follow-up Monitoring' => 'Pemantauan Lanjutan',
                default => $request->status,
            };
            $msg = "Safora Info:\nStatus laporan Anda #".strtoupper(substr($report->id, 0, 8))." ({$report->category}) telah diperbarui menjadi *{$statusLabel}*.\n\nTautan tracking: " . url('/tracking/' . $report->id);
            try {
                FonnteService::send($report->user->phone, $msg);
            } catch (\Exception $e) {}
        }

        return back()->with('success', 'Status laporan diperbarui ke "' . $request->status . '".');
    }

    public function accept(Request $request, $id)
    {
        $request->validate([]);

        $mitraId = auth()->user()->mitra_id;

        $phoneToSend = null;
        $messageToSend = null;

        try {
            $redirectMitraId = $mitraId;

            DB::transaction(function () use ($id, $mitraId, &$redirectMitraId, &$phoneToSend, &$messageToSend) {
                $report = Report::with(['user'])
                    ->where('id', $id)
                    ->whereNull('handler_mitra_id')
                    ->firstOrFail();

                $routing = ReportMitraRouting::query()
                    ->where('report_id', $report->id)
                    ->where('mitra_id', $mitraId)
                    ->where('status', 'pending')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->firstOrFail();

                $routing->update([
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);

                ReportMitraRouting::query()
                    ->where('report_id', $report->id)
                    ->where('id', '!=', $routing->id)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'expired',
                        'responded_at' => now(),
                    ]);

                $oldStatus = $report->status;
                $report->update([
                    'status' => 'Assigned',
                    'routed_mitra_id' => $mitraId,
                    'handler_mitra_id' => $mitraId,
                    'handler_user_id' => auth()->id(),
                    'assigned_at' => now(),
                    'last_activity_at' => now(),
                ]);

                ReportStatusLog::create([
                    'report_id' => $report->id,
                    'old_status' => $oldStatus,
                    'new_status' => 'Assigned',
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                ]);

                ReportStatusLog::create([
                    'report_id' => $report->id,
                    'old_status' => 'Assigned',
                    'new_status' => 'In Progress',
                    'changed_by' => auth()->id(),
                    'changed_at' => now(),
                ]);

                $report->update(['status' => 'In Progress']);

                AuditLog::log('accept_report', 'report', $report->id);

                if (!$report->user_id) {
                    return;
                }

                $thread = ChatThread::firstOrCreate(
                    ['user_id' => $report->user_id, 'mitra_id' => $mitraId],
                    ['id' => (string) Str::uuid(), 'last_message_at' => now()]
                );

                $mitra = Mitra::find($mitraId);

                ReportTimelineEvent::create([
                    'report_id' => $report->id,
                    'event_type' => 'mitra_accepted',
                    'event_message' => 'Kasus Anda sekarang ditangani oleh ' . ($mitra?->mitra_name ?? 'mitra Safora') . '. Anda sudah kami hubungkan ke chat krisis.',
                    'actor_type' => 'mitra',
                    'actor_id' => $mitraId,
                    'metadata' => [
                        'mitra_name' => $mitra?->mitra_name,
                        'mitra_type' => $mitra?->mitra_type,
                    ],
                ]);

                ReportTimelineEvent::create([
                    'report_id' => $report->id,
                    'event_type' => 'user_connected_to_chat',
                    'event_message' => 'Chat krisis sudah dibuka. Kirim pesan singkat bila Anda bisa, atau tetap pantau halaman tracking.',
                    'actor_type' => 'system',
                ]);

                ChatMessage::create([
                    'chat_thread_id' => $thread->id,
                    'sender_type' => 'mitra',
                    'sender_id' => $mitraId,
                    'message' => "Halo, laporan Anda dengan kategori {$report->category} telah diterima oleh {$mitra?->mitra_name}. Tim kami siap membantu Anda.",
                ]);

                $thread->update(['last_message_at' => now()]);

                if ($report->report_type !== 'past_incident' && $report->user?->phone) {
                    $phoneToSend = $report->user->phone;
                    $messageToSend =
                        "Safora: Laporan Anda dengan kategori {$report->category} telah diterima oleh {$mitra?->mitra_name}. " .
                        "Silakan buka chat untuk koordinasi lanjutan.";
                }

            });

            // Send WhatsApp outside database transaction to prevent deadlocks
            if ($phoneToSend && $messageToSend) {
                try {
                    FonnteService::send($phoneToSend, $messageToSend);
                } catch (\Exception $e) {
                    // skip
                }
            }

            return redirect()->route('mitra.show', $id)->with('success', 'Laporan berhasil diterima.');
        } catch (\Exception $e) {
            return back()->with('error', 'Laporan tidak dapat diterima. Mungkin sudah expired atau diambil mitra lain.');
        }
    }

    public function updateProfile(Request $request)
    {
        $mitraId = auth()->user()->mitra_id;
        $mitra = Mitra::findOrFail($mitraId);

        $request->validate([
            'catatan' => 'nullable|string|max:5000',
            'bank_name' => 'nullable|string|max:255',
            'nomor_rekening' => 'nullable|string|max:255',
            'ewallet_name' => 'nullable|string|max:255',
            'nomor_ewallet' => 'nullable|string|max:255',
        ]);

        $mitra->update($request->only([
            'catatan', 'bank_name', 'nomor_rekening', 'ewallet_name', 'nomor_ewallet'
        ]));

        return back()->with('success', 'Profil dan metode pembayaran berhasil diperbarui.');
    }

    public function storePriceList(Request $request)
    {
        $mitraId = auth()->user()->mitra_id;

        $request->validate([
            'service_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:255',
        ]);

        $duration = $request->duration;
        if ($duration && !\Illuminate\Support\Str::contains(\Illuminate\Support\Str::lower($duration), ['sesi', 'session'])) {
            $duration = null;
        }

        \App\Models\PriceList::create([
            'mitra_id' => $mitraId,
            'service_name' => $request->service_name,
            'price' => $request->price,
            'duration' => $duration,
            'currency' => 'IDR',
        ]);

        return back()->with('success', 'Layanan pricelist baru berhasil ditambahkan.');
    }

    public function destroyPriceList($id)
    {
        $mitraId = auth()->user()->mitra_id;
        $priceList = \App\Models\PriceList::where('id', $id)
            ->where('mitra_id', $mitraId)
            ->firstOrFail();

        $priceList->delete();

        return back()->with('success', 'Layanan pricelist berhasil dihapus.');
    }

    public function showClientDetails($userId)
    {
        $mitraId = auth()->user()->mitra_id;
        $mitra = Mitra::findOrFail($mitraId);

        $client = \App\Models\User::findOrFail($userId);
        $location = \App\Models\UserLocation::where('user_id', $userId)->first();
        
        $purchasedServices = \App\Models\UserMitraPayment::query()
            ->with('priceList')
            ->where('user_id', $userId)
            ->where('mitra_id', $mitraId)
            ->latest('paid_at')
            ->get();

        return view('pages.mitra.client_details', compact('mitra', 'client', 'location', 'purchasedServices'));
    }
}
