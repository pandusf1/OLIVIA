<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evidence;
use App\Models\Report;

class EvidenceController extends Controller
{
    public function index()
    {
        $reports = Report::where('user_id', auth()->id())
            ->whereHas('evidences')
            ->with('evidences')
            ->latest()
            ->get();

        return view('pages.evidence', compact('reports'));
    }

    public function store(Request $request, $reportId)
    {
        $report = Report::findOrFail($reportId);
        
        // If step 2 sends description/show_evidence
        if ($request->has('description') || $request->has('show_evidence')) {
            if ($request->has('description')) {
                $report->description = $request->description;
            }
            if ($request->has('show_evidence')) {
                $report->show_evidence = filter_var($request->show_evidence, FILTER_VALIDATE_BOOLEAN);
            }
            $report->save();
        }

        $uploaded = [];
        if ($request->hasFile('evidence')) {
            $request->validate([
                'evidence' => 'required|array',
                'evidence.*' => 'file', // no size limit
            ]);

            $files = $request->file('evidence');
            
            // Determine uploader role
            $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
                || in_array($reportId, session()->get('my_reports', []));
            
            $isPartner = auth()->check() && auth()->user()->role === 'partner';
            if ($isPartner) {
                $partnerId = auth()->user()->partner_id;
                if ($report->handler_partner_id !== null && $report->handler_partner_id !== $partnerId) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'Akses ditolak. Kasus ini ditangani oleh mitra lain.'
                    ], 403);
                }
            }
            
            $uploaderRole = 'Saksi';
            if ($isCreator) {
                $uploaderRole = 'Korban';
            } elseif ($isPartner) {
                $uploaderRole = 'Mitra';
            }

            foreach ($files as $file) {
                try {
                    $path = null;
                    $hash = Evidence::generateFastHash($file->getRealPath(), $file->getClientOriginalName(), $file->getSize());

                    try {
                        $path = $file->store('evidences', 'public');
                    } catch (\Throwable $storeEx) {
                        $fileData = file_get_contents($file->getRealPath());
                        $mimeType = $file->getClientMimeType();
                        $base64 = base64_encode($fileData);
                        $path = 'data:' . $mimeType . ';base64,' . $base64;
                    }

                    $evidence = Evidence::create([
                        'report_id'   => $reportId,
                        'file_url'    => $path,
                        'file_type'   => $file->getClientMimeType(),
                        'file_hash'   => $hash,
                        'uploaded_by' => auth()->id(),
                        'uploaded_at' => now(),
                        'uploaded_ip' => $request->ip(),
                        'device_info' => $request->userAgent(),
                        'uploader_role' => $uploaderRole,
                    ]);

                    $uploaded[] = [
                        'id' => $evidence->id,
                        'file_url' => str_starts_with($evidence->file_url, 'data:') ? $evidence->file_url : url('/evidences/view/' . basename($evidence->file_url)),
                        'file_type' => $evidence->file_type,
                        'uploader_role' => $evidence->uploader_role,
                    ];
                } catch (\Exception $e) {
                    \Log::error("Evidence upload error: " . $e->getMessage());
                    return response()->json([
                        'ok' => false,
                        'error' => 'Gagal upload: ' . $e->getMessage()
                    ], 500);
                }
            }
        }

        if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['ok' => true, 'evidences' => $uploaded]);
        }

        return back()->with('success', 'Bukti berhasil diupload dan diamankan dengan SHA-256.');
    }

    public function destroy($id)
    {
        $evidence = Evidence::findOrFail($id);
        $report = Report::findOrFail($evidence->report_id);

        $isCreator = (auth()->check() && auth()->id() === $report->user_id) 
            || in_array($report->id, session()->get('my_reports', []));

        if (!$isCreator) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        // Delete from public storage
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($evidence->file_url)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($evidence->file_url);
        }

        $evidence->delete();

        return response()->json(['ok' => true]);
    }
}
