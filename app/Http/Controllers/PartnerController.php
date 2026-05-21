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
    public function index()
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

        return view('pages.partner.index', compact('partner', 'pendingRoutings', 'activeReports', 'resolvedReports', 'stats'));
    }

    public function show($id)
    {
        $report = Report::with([
            'evidences',
            'statusLogs',
            'witnessReports.evidences',
        ])->findOrFail($id);

        $partnerId = auth()->user()->partner_id;
        $hasRoutingAccess = $report->partnerRoutings()
            ->where('partner_id', $partnerId)
            ->exists();

        if ($report->routed_partner_id !== $partnerId && !$hasRoutingAccess) {
            abort(403, 'Laporan ini bukan untuk mitra anda.');
        }

        // Catat bahwa mitra telah melihat laporan
        if ($report->status === 'Routed') {
            ReportPartnerRouting::query()
                ->where('report_id', $report->id)
                ->where('partner_id', $partnerId)
                ->where('status', 'pending')
                ->update([
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

        return view('pages.partner.show', compact('report'));
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
                    'sender_type' => 'system',
                    'sender_id' => auth()->id(),
                    'message' => "Halo, laporan Anda dengan kategori {$report->category} telah diterima oleh {$partner?->partner_name}. Tim kami siap membantu Anda.",
                ]);

                $thread->update(['last_message_at' => now()]);

                if ($report->user?->phone) {
                    $message =
                        "Safora: laporan Anda dengan kategori {$report->category} telah diterima oleh {$partner?->partner_name}. " .
                        "Silakan buka chat untuk koordinasi lanjutan.";

                    try {
                        FonnteService::send($report->user->phone, $message);
                    } catch (\Exception $e) {
                        // skip jika layanan WA tidak tersedia
                    }
                }

                $redirectPartnerId = $partnerId;
            });

            return redirect('/chat/messages/' . $redirectPartnerId . '?report_id=' . $id);
        } catch (\Exception $e) {
            return back()->with('error', 'Laporan tidak dapat diterima. Mungkin sudah expired atau diambil mitra lain.');
        }
    }
    
}
