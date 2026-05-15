<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\Evidence;
use Illuminate\Support\Facades\Auth;

class PastReportController extends Controller
{
    public function create()
    {
        return view('pages.report.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'location_text' => 'required|string|max:255',
            'anonymous' => 'nullable|boolean',
            'evidences.*' => 'nullable|file|max:20480',
        ]);

        $report = Report::create([
            'user_id' => Auth::id(),
            'report_type' => 'past_incident',
            'category' => $request->category,
            'description' => $request->description,
            'location_text' => $request->location_text,
            'anonymous' => $request->has('anonymous') ? 1 : 0,
            'status' => 'Submitted',
        ]);

        if ($request->hasFile('evidences')) {
            foreach ($request->file('evidences') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('evidences', 'public');
                    $hash = hash_file('sha256', $file->getRealPath());

                    Evidence::create([
                        'report_id'   => $report->id,
                        'file_url'    => $path,
                        'file_type'   => $file->getClientMimeType(),
                        'file_hash'   => $hash,
                        'uploaded_by' => Auth::id(),
                        'uploaded_at' => now(),
                        'uploaded_ip' => $request->ip(),
                        'device_info' => $request->userAgent(),
                    ]);
                }
            }
        }

        return redirect()->route('dashboard')->with('success', 'Laporan berhasil dibuat. Bukti telah diamankan.');
    }
}
