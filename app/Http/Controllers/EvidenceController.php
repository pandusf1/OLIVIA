<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evidence;
use App\Models\Report;

class EvidenceController extends Controller
{
    public function index(Request $request)
    {
        $reports = Report::where('user_id', auth()->id())
            ->whereHas('evidences')
            ->with('evidences')
            ->latest()
            ->get();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'reports' => $reports
            ]);
        }

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
                || ($report->user_id === null && in_array($reportId, session()->get('my_reports', [])));
            
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
                    $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';
                    $realPath = $file->getRealPath();

                    // Compress image using TinyPNG API if applicable
                    $fileData = Evidence::compressImageIfNeeded($realPath, $mimeType);
                    $hash = hash('sha256', $fileData);

                    $path = null;
                    try {
                        // Attempt to write the compressed data to public disk
                        $tempPath = tempnam(sys_get_temp_dir(), 'evidence_');
                        file_put_contents($tempPath, $fileData);
                        $path = \Illuminate\Support\Facades\Storage::disk('public')->putFile('evidences', new \Illuminate\Http\File($tempPath));
                        @unlink($tempPath);
                    } catch (\Throwable $storeEx) {
                        $base64 = base64_encode($fileData);
                        $path = 'data:' . $mimeType . ';base64,' . $base64;
                    }

                    $evidence = \Illuminate\Support\Facades\DB::transaction(function () use ($reportId, $path, $mimeType, $hash, $request, $uploaderRole) {
                        return Evidence::create([
                            'report_id'   => $reportId,
                            'file_url'    => $path,
                            'file_type'   => $mimeType,
                            'file_hash'   => $hash,
                            'uploaded_by' => auth()->id(),
                            'uploaded_at' => now(),
                            'uploaded_ip' => $request->ip(),
                            'device_info' => $request->userAgent(),
                            'uploader_role' => $uploaderRole,
                        ]);
                    });

                    session()->push('uploaded_evidence_ids', $evidence->id);

                    $uploaded[] = [
                        'id' => $evidence->id,
                        'file_url' => url('/evidences/view/' . $evidence->id),
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
            || ($report->user_id === null && in_array($report->id, session()->get('my_reports', [])));

        $isUploader = in_array($id, session()->get('uploaded_evidence_ids', []));

        if (!$isCreator && !$isUploader) {
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
