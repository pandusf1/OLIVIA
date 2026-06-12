<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Mitra;
use App\Models\ReportMitraRouting;
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
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses oleh mitra.');
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
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa diedit karena sudah lewat 15 menit atau sudah diproses oleh mitra.');
        }

        $request->validate([
            'category' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $oldCategory = $report->category;
        $report->category = $request->category;
        $report->description = $request->description;
        
        // Atur ulang waktu 15 menit dari pembuatan laporan
        $report->created_at = now();
        $report->save();

        // Teruskan/route ulang ke mitra setiap kali ada perubahan laporan (kategori atau deskripsi)
        if ($report->status !== 'Resolved' && $report->status !== 'Assigned' && $report->status !== 'In Progress') {
            // Hapus routing lama yang belum diterima agar tracking tidak menampilkan mitra dari kategori sebelumnya.
            ReportMitraRouting::where('report_id', $report->id)
                ->whereIn('status', ['pending', 'expired'])
                ->delete();

            $mitras = Mitra::routeMultipleByCategory(
                $report->category,
                5,
                $report->latitude ? (float) $report->latitude : null,
                $report->longitude ? (float) $report->longitude : null
            );

            if ($mitras->isEmpty()) {
                // Buat mitra dummy untuk Lembaga Sosial (Laporan Biasa)
                $dummyMitra = Mitra::firstOrCreate(
                    ['phone' => '080000000000'],
                    [
                        'mitra_name' => 'Lembaga Sosial Mitra Safora',
                        'mitra_type' => 'lembaga_sosial',
                        'city' => 'Semarang',
                        'verified' => true,
                        'is_active' => true,
                    ]
                );
                $mitras = collect([$dummyMitra]);
            }

            $expiryMinutes = (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 180);
            $expiresAt = now()->addMinutes(max(1, $expiryMinutes));

            $report->update([
                'status' => $mitras->isNotEmpty() ? 'Routed' : 'Submitted',
                'routed_mitra_id' => $mitras->first()?->id,
            ]);

            foreach ($mitras as $mitra) {
                ReportMitraRouting::create([
                    'report_id' => $report->id,
                    'mitra_id' => $mitra->id,
                    'status' => 'pending',
                    'routed_at' => now(),
                    'expires_at' => $expiresAt,
                    'distance_km' => ($report->latitude && $report->longitude && $mitra->latitude && $mitra->longitude)
                        ? Mitra::distanceKm((float) $report->latitude, (float) $report->longitude, (float) $mitra->latitude, (float) $mitra->longitude)
                        : null,
                    'estimated_response_minutes' => $mitra->mitra_type === 'ambulance' ? 5 : 8,
                ]);

                // Kirim notifikasi WhatsApp ke mitra baru
                if ($mitra->phone) {
                    $trackingLink = url('/tracking/' . $report->id);
                    $mapsLink = $report->latitude
                        ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
                        : 'Lokasi tidak tersedia';

                    $mitraMessage =
                        "🚨 *Safora - Laporan Diperbarui*\n\n" .
                        "Kategori Baru: *{$report->category}*\n\n" .
                        "📍 Lokasi:\n{$mapsLink}\n\n" .
                        "🔗 Tracking:\n{$trackingLink}\n\n" .
                        "Buka dashboard Safora untuk menerima laporan ini.";

                    try {
                        FonnteService::send($mitra->phone, $mitraMessage);
                    } catch (\Exception $e) {
                        // abaikan jika gagal
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
            return redirect()->route('dashboard')->with('error', 'Laporan tidak bisa dihapus karena sudah lewat 15 menit atau sudah diproses oleh mitra.');
        }

        $report->delete();

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dihapus.');
    }
}
