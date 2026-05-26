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

        if ($request->hasFile('evidence')) {
            $request->validate([
                'evidence' => 'required|array',
                'evidence.*' => 'file|max:20480',
            ]);

            $files = $request->file('evidence');
            
            foreach ($files as $file) {
                $path = $file->store('evidences', 'public');
                $hash = hash_file('sha256', $file->getRealPath());

                Evidence::create([
                    'report_id'   => $reportId,
                    'file_url'    => $path,
                    'file_type'   => $file->getClientMimeType(),
                    'file_hash'   => $hash,
                    'uploaded_by' => auth()->id(),
                    'uploaded_at' => now(),
                    'uploaded_ip' => $request->ip(),
                    'device_info' => $request->userAgent(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Bukti berhasil diupload dan diamankan dengan SHA-256.');
    }
}
