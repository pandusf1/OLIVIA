<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\AuditLog;
use App\Models\ChatMessage;
use App\Models\ChatThread;
use App\Models\Partner;
use App\Models\ReportPartnerRouting;
use App\Models\ReportTimelineEvent;
use App\Services\FonnteService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = auth()->user()->partner_id;

        $partner = Partner::findOrFail($partnerId);

        $pendingRoutings = ReportPartnerRouting::query()
            ->with(['report' => fn ($query) => $query->with(['user'])->withCount('evidences')])
            ->where('partner_id', $partnerId)
            ->where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->latest('routed_at')
            ->get();

        $activeReports = Report::query()
            ->with(['user', 'partnerRoutings' => fn ($query) => $query->where('partner_id', $partnerId)])
            ->withCount('evidences')
            ->where('status', 'In Progress')
            ->whereHas('partnerRoutings', function ($query) use ($partnerId) {
                $query->where('partner_id', $partnerId)->where('status', 'accepted');
            })
            ->latest('updated_at')
            ->get();

        $resolvedReports = Report::query()
            ->with(['user', 'partnerRoutings' => fn ($query) => $query->where('partner_id', $partnerId)])
            ->withCount('evidences')
            ->where('status', 'Resolved')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->whereHas('partnerRoutings', function ($query) use ($partnerId) {
                $query->where('partner_id', $partnerId)->where('status', 'accepted');
            })
            ->latest('updated_at')
            ->get();

        $stats = [
            'pending' => $pendingRoutings->count(),
            'progress' => $activeReports->count(),
            'resolved_month' => $resolvedReports->count(),
        ];

        // For "Semua Laporan" Tab
        $allReportsQuery = Report::whereHas('partnerRoutings', function ($q) use ($partnerId) {
            $q->where('partner_id', $partnerId);
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
                $allReportsQuery->where('handler_partner_id', $partnerId);
            } elseif ($request->handled == 'no') {
                $allReportsQuery->where(function($q) use ($partnerId) {
                    $q->whereNull('handler_partner_id')
                      ->orWhere('handler_partner_id', '!=', $partnerId);
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

        $allReports = $allReportsQuery->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('pages.partner.index', compact('partner', 'pendingRoutings', 'activeReports', 'resolvedReports', 'stats', 'allReports'));
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
            'witnessReports.evidences',
        ]);

        $partnerId = auth()->user()->partner_id;
        $routing = $report->partnerRoutings()
            ->where('partner_id', $partnerId)
            ->first();

        if ($report->routed_partner_id !== $partnerId && !$routing) {
            abort(403, 'Laporan ini bukan untuk mitra anda.');
        }

        $isHandling = ($report->handler_partner_id === $partnerId);
        $isPending = ($routing && $routing->status === 'pending' && (is_null($routing->expires_at) || $routing->expires_at > now()));
        $canViewSensitive = $isHandling || $isPending;

        // Catat bahwa mitra telah melihat laporan
        if ($report->status === 'Routed' && $isPending) {
            $routing->update([
                'reviewed_at' => now(),
            ]);

            ReportTimelineEvent::create([
                'report_id' => $report->id,
                'event_type' => 'partner_reviewing',
                'event_message' => 'Salah satu partner sedang meninjau laporan Anda.',
                'actor_type' => 'partner',
                'actor_id' => $partnerId,
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

        return view('pages.partner.show', compact('report', 'canViewSensitive', 'isHandling'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string|in:Submitted,Routed,Viewed,Assigned,In Progress,Resolved,Follow-up Monitoring']);

        $report = Report::findOrFail($id);
        $partnerId = auth()->user()->partner_id;
        $hasAcceptedRouting = $report->partnerRoutings()
            ->where('partner_id', $partnerId)
            ->where('status', 'accepted')
            ->exists();

        if ($report->routed_partner_id !== $partnerId && !$hasAcceptedRouting) {
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
            'actor_type' => 'partner',
            'actor_id' => $partnerId,
        ]);

        return back()->with('success', 'Status laporan diperbarui ke "' . $request->status . '".');
    }

    public function accept(Request $request, $id)
    {
        $request->validate([]);

        $partnerId = auth()->user()->partner_id;

        try {
            $redirectPartnerId = $partnerId;

            DB::transaction(function () use ($id, $partnerId, &$redirectPartnerId) {
                $report = Report::with(['user'])
                    ->where('id', $id)
                    ->whereNull('handler_partner_id')
                    ->lockForUpdate()
                    ->firstOrFail();

                $routing = ReportPartnerRouting::query()
                    ->where('report_id', $report->id)
                    ->where('partner_id', $partnerId)
                    ->where('status', 'pending')
                    ->where(function ($query) {
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->lockForUpdate()
                    ->firstOrFail();

                $routing->update([
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);

                ReportPartnerRouting::query()
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
                    'routed_partner_id' => $partnerId,
                    'handler_partner_id' => $partnerId,
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
                    ['user_id' => $report->user_id, 'partner_id' => $partnerId],
                    ['id' => (string) Str::uuid(), 'last_message_at' => now()]
                );

                $partner = Partner::find($partnerId);

                ReportTimelineEvent::create([
                    'report_id' => $report->id,
                    'event_type' => 'partner_accepted',
                    'event_message' => 'Kasus Anda sekarang ditangani oleh ' . ($partner?->partner_name ?? 'partner Safora') . '. Anda sudah kami hubungkan ke chat krisis.',
                    'actor_type' => 'partner',
                    'actor_id' => $partnerId,
                    'metadata' => [
                        'partner_name' => $partner?->partner_name,
                        'partner_type' => $partner?->partner_type,
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
                    'sender_type' => 'partner',
                    'sender_id' => $partnerId,
                    'message' => "Halo, laporan Anda dengan kategori {$report->category} telah diterima oleh {$partner?->partner_name}. Tim kami siap membantu Anda.",
                ]);

                $thread->update(['last_message_at' => now()]);

                if ($report->report_type !== 'past_incident' && $report->user?->phone) {
                    $message =
                        "Safora: laporan Anda dengan kategori {$report->category} telah diterima oleh {$partner?->partner_name}. " .
                        "Silakan buka chat untuk koordinasi lanjutan.";

                    try {
                        FonnteService::send($report->user->phone, $message);
                    } catch (\Exception $e) {
                        // skip jika layanan WA tidak tersedia
                    }
                }

            });

            return redirect()->route('partner.show', $id)->with('success', 'Laporan berhasil diterima.');
        } catch (\Exception $e) {
            return back()->with('error', 'Laporan tidak dapat diterima. Mungkin sudah expired atau diambil mitra lain.');
        }
    }
    
}
