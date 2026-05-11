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
            ->with('evidences')
            ->withCount('evidences')
            ->having('evidences_count', '>', 0)
            ->latest()
            ->get();

        return view('pages.evidence', compact('reports'));
    }

    public function store(Request $request, $reportId)
    {
        $request->validate([
            'evidence' => 'required|file|max:20480',
        ]);

        $file = $request->file('evidence');
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

        return back()->with('success', 'Bukti berhasil diupload dan diamankan dengan SHA-256.');
    }
}
