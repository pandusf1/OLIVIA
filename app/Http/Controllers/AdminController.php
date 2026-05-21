<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Partner;
use App\Models\AuditLog;
use App\Models\ReportPartnerRouting;
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
            ->whereDoesntHave('partnerRoutings', fn ($query) => $query->where('status', 'accepted'))
            ->whereDoesntHave('partnerRoutings', $validPending);

        $stats = [
            'reports' => Report::count(),
            'today' => Report::whereDate('created_at', today())->count(),
            'emergency' => Report::where('report_type', 'Emergency')->count(),
            'unhandled' => (clone $unhandledQuery)->count(),
            'resolved' => Report::where('status', 'Resolved')->count(),
            'active_partners' => Partner::query()
                ->when(\Illuminate\Support\Facades\Schema::hasColumn('partners', 'is_active'), fn ($query) => $query->where('is_active', true))
                ->count(),
        ];

        $unhandledReports = (clone $unhandledQuery)
            ->latest()
            ->take(10)
            ->get();

        $partners = Partner::query()
            ->with(['reportRoutings.report'])
            ->latest()
            ->get()
            ->map(function ($partner) {
                $accepted = $partner->reportRoutings->where('status', 'accepted');
                $responseMinutes = $accepted
                    ->filter(fn ($routing) => $routing->routed_at && $routing->responded_at)
                    ->map(fn ($routing) => $routing->routed_at->diffInMinutes($routing->responded_at));

                $partner->accepted_count = $accepted->count();
                $partner->average_response_minutes = $responseMinutes->count() ? round($responseMinutes->avg()) : null;
                $partner->active_reports_count = $accepted->filter(fn ($routing) => $routing->report?->status === 'In Progress')->count();
                $partner->last_response_at = optional($accepted->sortByDesc('responded_at')->first())->responded_at;
                $partner->activity_status = $partner->last_response_at && $partner->last_response_at->greaterThanOrEqualTo(now()->subDays(7))
                    ? 'Aktif'
                    : 'Tidak Aktif';

                return $partner;
            });

        $reportsQuery = Report::query()
            ->with(['user', 'partner', 'partnerRoutings.partner']);

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
        if (request('partner_id')) {
            $partnerId = request('partner_id');
            $reportsQuery->whereHas('partnerRoutings', fn ($query) => $query->where('partner_id', $partnerId));
        }

        $reports = $reportsQuery->latest()->take(50)->get();
        $auditLogs = AuditLog::latest('created_at')->take(20)->get();
        $categories = Report::query()->select('category')->distinct()->pluck('category');

        return view('pages.admin.index', compact('stats', 'unhandledReports', 'partners', 'reports', 'auditLogs', 'categories'));
    }

    public function rerouteReport(Request $request, $id)
    {
        $request->validate([]);

        $report = Report::findOrFail($id);
        $partners = Partner::routeMultipleByCategory($report->category, 5, $report->latitude, $report->longitude);
        $expiresAt = now()->addMinutes(max(1, (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 30)));

        foreach ($partners as $partner) {
            ReportPartnerRouting::create([
                'report_id' => $report->id,
                'partner_id' => $partner->id,
                'status' => 'pending',
                'routed_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            if ($partner->phone) {
                $mapsLink = $report->latitude ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}" : 'Lokasi tidak tersedia';
                $message =
                    "Safora: laporan darurat kategori {$report->category} diroute ulang oleh admin.\n\n" .
                    "Lokasi: {$mapsLink}\n" .
                    "Tracking: " . url('/tracking/' . $report->id) . "\n\n" .
                    "Buka dashboard Safora untuk menerima laporan ini.";

                try {
                    FonnteService::send($partner->phone, $message);
                } catch (\Exception $e) {
                    // skip jika layanan WA tidak tersedia
                }
            }
        }

        $oldStatus = $report->status;
        $report->update([
            'status' => $partners->isNotEmpty() ? 'Routed' : $report->status,
            'routed_partner_id' => $partners->first()?->id ?? $report->routed_partner_id,
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

        return back()->with('success', 'Laporan berhasil diroute ulang ke ' . $partners->count() . ' partner.');
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

    public function partners()
    {
        $partners = Partner::latest()->get();

        return view('pages.admin.partners.index', compact('partners'));
    }

    public function createPartner()
{
    return view('pages.admin.partners.create');
}

public function storePartner(Request $request)
{
    $request->validate([
        'partner_name' => 'required|string|max:255',
        'partner_type' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:30',
    ]);

    $partner = Partner::create([
        'partner_name' => $request->partner_name,
        'partner_type' => $request->partner_type,
        'city' => $request->city,
        'phone' => $request->phone,
        'email' => $request->email,
        'verified' => true,
    ]);

    $password = Str::random(10);

    User::create([
        'name' => $request->partner_name,
        'email' => $request->email,
        'password' => Hash::make($password),
        'role' => 'partner',
        'partner_id' => $partner->id,
    ]);

    return redirect()
        ->route('admin.partners')
        ->with('success', 'Partner berhasil dibuat. Password: ' . $password);
}

public function verifyPartner($id)
{
    $partner = Partner::findOrFail($id);

    $partner->verified = !$partner->verified;

    $partner->save();

    return back()->with('success', 'Status partner diperbarui.');
}
}
