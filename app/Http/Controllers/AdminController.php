<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Mitra;
use App\Models\AuditLog;
use App\Models\ReportMitraRouting;
use App\Models\ReportStatusLog;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $validPending = fn ($query) => $query
            ->where('status', 'pending')
            ->where(function ($pending) {
                $pending->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });

        $unhandledQuery = Report::query()
            ->whereDoesntHave('mitraRoutings', fn ($query) => $query->where('status', 'accepted'))
            ->whereDoesntHave('mitraRoutings', $validPending);

        $stats = [
            'reports' => Report::count(),
            'today' => Report::whereDate('created_at', today())->count(),
            'emergency' => Report::where('report_type', 'Emergency')->count(),
            'unhandled' => (clone $unhandledQuery)->count(),
            'resolved' => Report::where('status', 'Resolved')->count(),
            'active_mitras' => Mitra::query()
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('mitras', 'is_active'), fn ($query) => $query->where('is_active', true))
                ->count(),
        ];

        $unhandledReports = (clone $unhandledQuery)
            ->latest()
            ->take(10)
            ->get();

        $mitras = Mitra::query()
            ->with(['reportRoutings.report'])
            ->latest()
            ->get()
            ->map(function ($mitra) {
                $accepted = $mitra->reportRoutings->where('status', 'accepted');
                $responseMinutes = $accepted
                    ->filter(fn ($routing) => $routing->routed_at && $routing->responded_at)
                    ->map(fn ($routing) => $routing->routed_at->diffInMinutes($routing->responded_at));

                $mitra->accepted_count = $accepted->count();
                $mitra->average_response_minutes = $responseMinutes->count() ? round($responseMinutes->avg()) : null;
                $mitra->active_reports_count = $accepted->filter(fn ($routing) => $routing->report?->status === 'In Progress')->count();
                $mitra->last_response_at = optional($accepted->sortByDesc('responded_at')->first())->responded_at;
                $mitra->activity_status = $mitra->last_response_at && $mitra->last_response_at->greaterThanOrEqualTo(now()->subDays(7))
                    ? 'Aktif'
                    : 'Tidak Aktif';

                return $mitra;
            });

        $reportsQuery = Report::query()
            ->with(['user', 'mitra', 'mitraRoutings.mitra']);

        if (request('status')) {
            $reportsQuery->where('status', request('status'));
        }
        if (request('category')) {
            $reportsQuery->where('category', request('category'));
        }
        if (request('report_type')) {
            $reportsQuery->where('report_type', request('report_type'));
        }
        if (request('date_from')) {
            $reportsQuery->whereDate('created_at', '>=', request('date_from'));
        }
        if (request('date_to')) {
            $reportsQuery->whereDate('created_at', '<=', request('date_to'));
        }
        if (request('mitra_id')) {
            $mitraId = request('mitra_id');
            $reportsQuery->whereHas('mitraRoutings', fn ($query) => $query->where('mitra_id', $mitraId));
        }

        $reports = $reportsQuery->latest()->take(50)->get();
        $auditLogs = AuditLog::latest('created_at')->take(20)->get();
        $categories = Report::query()->select('category')->distinct()->pluck('category');

        return view('pages.admin.index', compact('stats', 'unhandledReports', 'mitras', 'reports', 'auditLogs', 'categories'));
    }

    public function rerouteReport(Request $request, $id)
    {
        $request->validate([]);

        $report = Report::findOrFail($id);
        $mitras = Mitra::routeMultipleByCategory($report->category, 5, $report->latitude, $report->longitude);
        $expiresAt = now()->addMinutes(max(1, (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 30)));

        ReportMitraRouting::where('report_id', $report->id)
            ->whereIn('status', ['pending', 'expired'])
            ->delete();

        foreach ($mitras as $mitra) {
            ReportMitraRouting::create([
                'report_id' => $report->id,
                'mitra_id' => $mitra->id,
                'status' => 'pending',
                'routed_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            if ($mitra->phone) {
                $mapsLink = $report->latitude ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}" : 'Lokasi tidak tersedia';
                $message =
                    "Safora: Laporan darurat kategori {$report->category} diroute ulang oleh admin.\n\n" .
                    "Lokasi: {$mapsLink}\n" .
                    "Tracking: " . url('/tracking/' . $report->id) . "\n\n" .
                    "Buka dashboard Safora untuk menerima laporan ini.";

                try {
                    FonnteService::send($mitra->phone, $message);
                } catch (\Exception $e) {
                    // skip jika layanan WA tidak tersedia
                }
            }
        }

        // Juga kirim ke ADMIN_PHONE sebagai testing jika dalam mode testing
        if (env('ADMIN_PHONE') && $mitras->isNotEmpty()) {
            $mitra = $mitras->first();
            $mapsLink = $report->latitude ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}" : 'Lokasi tidak tersedia';
            $message =
                "Safora: Laporan darurat kategori {$report->category} diroute ulang oleh admin (Test Mitra).\n\n" .
                "Lokasi: {$mapsLink}\n" .
                "Tracking: " . url('/tracking/' . $report->id) . "\n\n" .
                "Buka dashboard Safora untuk menerima laporan ini.";

            try {
                FonnteService::send(env('ADMIN_PHONE'), $message);
            } catch (\Exception $e) {
                // skip jika layanan WA tidak tersedia
            }
        }

        $oldStatus = $report->status;
        $report->update([
            'status' => $mitras->isNotEmpty() ? 'Routed' : $report->status,
            'routed_mitra_id' => $mitras->first()?->id,
        ]);

        if ($oldStatus !== $report->status) {
            ReportStatusLog::create([
                'report_id' => $report->id,
                'old_status' => $oldStatus,
                'new_status' => $report->status,
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        AuditLog::log('manual_reroute_report', 'report', $report->id);

        return back()->with('success', 'Laporan berhasil diroute ulang ke ' . $mitras->count() . ' mitra.');
    }

    public function resolveReport(Request $request, $id)
    {
        $request->validate([]);

        $report = Report::findOrFail($id);
        $oldStatus = $report->status;
        $report->update(['status' => 'Resolved']);

        ReportStatusLog::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => 'Resolved',
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        AuditLog::log('manual_resolve_report', 'report', $report->id);

        return back()->with('success', 'Laporan ditandai selesai secara manual.');
    }
}
