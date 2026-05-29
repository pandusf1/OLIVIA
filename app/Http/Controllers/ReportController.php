<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Partner;
use App\Models\ReportPartnerRouting;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function edit($id)
    {
        $report = Report::where('user_id', auth()->id())->findOrFail($id);

        if ($report->created_at->diffInMinutes(now()) > 15 || in_array($report->status, ['Assigned', 'In Progress', 'Resolved'])) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses oleh partner.');
        }

        return view('pages.report.edit', compact('report'));
    }

    public function update(Request $request, $id)
    {
        $report = Report::where('user_id', auth()->id())->findOrFail($id);

        if ($report->created_at->diffInMinutes(now()) > 15 || in_array($report->status, ['Assigned', 'In Progress', 'Resolved'])) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses oleh partner.');
        }

        $request->validate([
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $oldCategory = $report->category;
        $report->category = $request->category;
        $report->description = $request->description;
        
        // Reset the 15-minute timer
        $report->created_at = now();
        $report->save();

        // Teruskan/route ulang ke partner setiap kali ada perubahan laporan (kategori atau deskripsi)
        if ($report->status !== 'Resolved' && $report->status !== 'Assigned' && $report->status !== 'In Progress') {
            // Hapus routing lama yang belum diterima agar tracking tidak menampilkan partner dari kategori sebelumnya.
            ReportPartnerRouting::where('report_id', $report->id)
                ->whereIn('status', ['pending', 'expired'])
                ->delete();

            $partners = Partner::routeMultipleByCategory(
                $report->category,
                5,
                $report->latitude ? (float) $report->latitude : null,
                $report->longitude ? (float) $report->longitude : null
            );

            if ($partners->isEmpty()) {
                // Create dummy partner for "Lembaga Sosial" (Laporan Biasa)
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
                'status' => $partners->isNotEmpty() ? 'Routed' : 'Submitted',
                'routed_partner_id' => $partners->first()?->id,
            ]);

            foreach ($partners as $partner) {
                ReportPartnerRouting::create([
                    'report_id' => $report->id,
                    'partner_id' => $partner->id,
                    'status' => 'pending',
                    'routed_at' => now(),
                    'expires_at' => $expiresAt,
                    'distance_km' => ($report->latitude && $report->longitude && $partner->latitude && $partner->longitude)
                        ? Partner::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $partner->latitude, (float) $partner->longitude)
                        : null,
                    'estimated_response_minutes' => $partner->partner_type === 'ambulance' ? 5 : 8,
                ]);

                // Send WA Notification to new partner
                if ($partner->phone) {
                    $trackingLink = url('/tracking/' . $report->id);
                    $mapsLink = $report->latitude
                        ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
                        : 'Lokasi tidak tersedia';

                    $partnerMessage =
                        "🚨 *Safora - Laporan Diperbarui*\n\n" .
                        "Kategori Baru: *{$report->category}*\n\n" .
                        "📍 Lokasi:\n{$mapsLink}\n\n" .
                        "🔗 Tracking:\n{$trackingLink}\n\n" .
                        "Buka dashboard Safora untuk menerima laporan ini.";

                    try {
                        FonnteService::send($partner->phone, $partnerMessage);
                    } catch (\Exception $e) {
                        // skip
                    }
                }
            }
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => 'Laporan berhasil diperbarui.']);
        }

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $report = Report::where('user_id', auth()->id())->findOrFail($id);

        if ($report->created_at->diffInMinutes(now()) > 15 || in_array($report->status, ['Assigned', 'In Progress', 'Resolved'])) {
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa dihapus karena sudah lewat 15 menit atau sudah diproses oleh partner.');
        }

        $report->delete();

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dihapus.');
    }
}
