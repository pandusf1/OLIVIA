<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\ReportRouting;
use App\Models\Partner;
use App\Models\AuditLog;
use App\Services\FonnteService;

class EmergencyController extends Controller
{
    public function index()
    {
        return view('pages.emergency');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
        ]);

        $userId = null;

        try {
            $userId = auth()->id();
        } catch (\Exception $e) {
            $userId = null;
        }

        $report = Report::create([
            'user_id'           => $userId,
            'report_type'       => 'quick_emergency',
            'category'          => $request->category,
            'description'       => $request->description,
            'latitude'          => $request->latitude ?: null,
            'longitude'         => $request->longitude ?: null,
            'location_text'     => $request->location_text ?: null,
            'anonymous'         => $request->has('anonymous'),
            'status'            => 'Submitted',
            'routed_partner_id' => null,
        ]);

        ReportStatusLog::create([
            'report_id'  => $report->id,
            'old_status' => null,
            'new_status' => 'Submitted',
            'changed_by' => $userId,
            'changed_at' => now(),
        ]);

        try {
            AuditLog::log('create_report', 'report', $report->id);
        } catch (\Exception $e) {
            // skip jika offline
        }

        $trackingLink = url('/tracking/' . $report->id);
        $mapsLink     = $report->latitude
            ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
            : 'Lokasi tidak tersedia';

        // Alert ke admin / nomor utama
        $adminMessage =
            "🚨 *SURARA EMERGENCY ALERT*\n\n" .
            "Kategori: *{$report->category}*\n" .
            "Status: Submitted\n" .
            "Anonim: " . ($report->anonymous ? 'Ya' : 'Tidak') . "\n\n" .
            "📍 Lokasi:\n{$mapsLink}\n\n" .
            "🔗 Tracking:\n{$trackingLink}";

        try {
            FonnteService::send(env('ADMIN_PHONE', '6285124019353'), $adminMessage);
        } catch (\Exception $e) {
            // skip jika offline
        }

        // Smart routing: kirim ke mitra
        $this->routeToPartner($report);

        // Alert ke trusted contacts jika user login
        if ($userId) {
            $this->notifyTrustedContacts($report, $trackingLink, $mapsLink);
        }

        return redirect('/tracking/' . $report->id);
    }

private function routeToPartner(Report $report)
{
    $old = $report->status;

    $report->status = 'Routed';
    $report->save();

    ReportStatusLog::create([
        'report_id'  => $report->id,
        'old_status' => $old,
        'new_status' => 'Routed',
        'changed_by' => null,
        'changed_at' => now(),
    ]);

    $partner = null;

    if ($report->category == 'wrong_arrest') {
        $partner = Partner::where('partner_type', 'lbh')
            ->where('verified', true)
            ->first();
    }

    elseif ($report->category == 'harassment') {
        $partner = Partner::where('partner_type', 'psychologist')
            ->where('verified', true)
            ->first();
    }

    elseif ($report->category == 'accident') {
        $partner = Partner::where('partner_type', 'ambulance')
            ->where('verified', true)
            ->first();
    }

    if (!$partner) {
        $partner = Partner::where('verified', true)->first();
    }

    if ($partner) {
        $report->routed_partner_id = $partner->id;
        $report->save();

        ReportRouting::create([
            'report_id'  => $report->id,
            'partner_id' => $partner->id,
            'routed_at'  => now(),
        ]);
    }
}

    private function notifyTrustedContacts(Report $report, $trackingLink, $mapsLink)
    {
        $user = auth()->user();
        if (!$user) return;

        $contacts = $user->trustedContacts;
        if ($contacts->isEmpty()) return;

        foreach ($contacts as $contact) {
            $message =
                "🚨 *ALERT DARURAT — SuraRa*\n\n" .
                "{$user->name} memerlukan bantuan!\n\n" .
                "Kategori: *{$report->category}*\n\n" .
                "📍 Lokasi:\n{$mapsLink}\n\n" .
                "🔗 Pantau status:\n{$trackingLink}\n\n" .
                "_Pesan ini dikirim otomatis oleh SuraRa._";

            FonnteService::send($contact->contact_phone, $message);
        }
    }
}
