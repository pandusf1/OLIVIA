<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Evidence;
use App\Models\Partner;
use App\Models\ReportPartnerRouting;
use App\Services\FonnteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PastReportController extends Controller
{
    public function create()
    {
        return view('pages.report.create');
    }

    public function uploadEvidenceTemp(Request $request)
    {
        $request->validate([
            'evidence' => 'required|file|max:20480', // max 20MB
        ]);

        if ($request->hasFile('evidence')) {
            $file = $request->file('evidence');
            if ($file->isValid()) {
                $path = $file->store('temp_evidences', 'public');
                return response()->json([
                    'success' => true,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                ]);
            }
        }
        return response()->json(['success' => false], 400);
    }

    public function deleteEvidenceTemp(Request $request)
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $path = $request->input('path');
        if (str_starts_with($path, 'temp_evidences/')) {
            Storage::disk('public')->delete($path);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'location_text' => 'required|string|max:255',
            'incident_date' => 'required|date',
            'anonymous' => 'nullable|boolean',
            'evidences' => 'nullable|array',
            'evidences.*' => 'file|max:51200', // max 50MB
        ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'report_type' => 'past_incident',
            'category' => $request->category,
            'description' => $request->description,
            'location_text' => $request->location_text,
            'incident_date' => $request->incident_date,
            'anonymous' => $request->has('anonymous') ? 1 : 0,
            'status' => 'Submitted',
        ]);

        // Process directly uploaded files
        if ($request->hasFile('evidences')) {
            foreach ($request->file('evidences') as $file) {
                if ($file->isValid()) {
                    $newPath = $file->store('evidences', 'public');
                    
                    $fullPath = storage_path('app/public/' . $newPath);
                    $hash = file_exists($fullPath) ? hash_file('sha256', $fullPath) : null;
                    
                    $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';

                    Evidence::create([
                        'report_id'   => $report->id,
                        'file_url'    => $newPath,
                        'file_type'   => $mimeType,
                        'file_hash'   => $hash,
                        'uploaded_by' => Auth::id(),
                        'uploaded_at' => now(),
                        'uploaded_ip' => $request->ip(),
                        'device_info' => $request->userAgent(),
                    ]);
                }
            }
        }

        // Feature 2: Route to partner based on category
        $partners = Partner::routeMultipleByCategory($report->category, 5);
        
        if ($partners->isEmpty()) {
            // Create dummy partner for "Lembaga Sosial"
            $dummyPartner = Partner::firstOrCreate(
                ['phone' => '080000000000'],
                [
                    'partner_name' => 'Lembaga Sosial Mitra Safora',
                    'partner_type' => 'lembaga_sosial',
                    'city' => 'Semarang',
                    'verified' => true,
                    'is_active' => true,
                ]
            );
            $partners = collect([$dummyPartner]);
        }

        $expiryMinutes = (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 180);
        $expiresAt = now()->addMinutes(max(1, $expiryMinutes));

        $report->update([
            'status' => 'Routed',
            'routed_partner_id' => $partners->first()->id,
        ]);

        foreach ($partners as $partner) {
            ReportPartnerRouting::create([
                'report_id' => $report->id,
                'partner_id' => $partner->id,
                'status' => 'pending',
                'routed_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            // Notify partner via WA if available
            if ($partner->phone && $partner->phone !== '080000000000') {
                $trackingLink = url('/partner/report/' . $report->id);
                $partnerMessage =
                    "🚨 *Safora - Laporan Baru Diterima*\n\n" .
                    "Kategori: *{$report->category}*\n\n" .
                    "🔗 Buka dashboard mitra untuk merespons:\n{$trackingLink}";

                try {
                    FonnteService::send($partner->phone, $partnerMessage);
                } catch (\Exception $e) {
                    // skip
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dibuat dan diteruskan ke mitra terkait. Bukti telah diamankan.');
    }
}
