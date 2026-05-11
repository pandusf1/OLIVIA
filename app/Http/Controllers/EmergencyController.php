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

        $partner = Partner::routeByCategory($request->category);
        $report = Report::create([
            'user_id' => auth()->id(),
            'report_type' => 'Emergency',
            'category' => $request->category,
            'description' => $request->description,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_text' => $request->location_text,
            'anonymous' => $request->anonymous ?? false,

            'status' => $partner ? 'Routed' : 'Submitted',

            'routed_partner_id' => $partner?->id,
        ]);

        ReportStatusLog::create([
            'report_id'  => $report->id,
            'old_status' => null,
            'new_status' => 'Submitted',
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        if ($partner) {
            ReportStatusLog::create([
                'report_id' => $report->id,
                'old_status' => 'Submitted',
                'new_status' => 'Routed',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }
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

        // Alert ke trusted contacts jika user login
        if ($userId) {
            $this->notifyTrustedContacts($report, $trackingLink, $mapsLink);
        }

        return redirect('/tracking/' . $report->id);
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
