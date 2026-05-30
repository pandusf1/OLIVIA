<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\WitnessReport;
use App\Models\WitnessEvidence;
use App\Models\AuditLog;

class WitnessController extends Controller
{
    public function index()
    {
        return view('pages.witness.index');
    }

    public function store(Request $request)
    {
        $fromDashboard = $request->input('_from') === 'dashboard';

        $rules = [
            'report_id'     => 'required|string|exists:reports,id',
            'witness_note'  => 'nullable|string|max:1000',
            'witness_name'  => 'nullable|string|max:100',
            'witness_phone' => 'nullable|string|max:20',
            'evidence_file' => 'nullable|file', // no size limit
        ];

        if ($fromDashboard) {
            $request->validateWithBag('witness', $rules);
        } else {
            $request->validate($rules);
        }

        $report = Report::findOrFail($request->report_id);

        $witness = WitnessReport::create([
            'report_id'     => $report->id,
            'witness_name'  => $request->witness_name ?: null,
            'witness_phone' => $request->witness_phone ?: null,
            'witness_note'  => $request->witness_note ?: null,
            'created_at'    => now(),
        ]);

        AuditLog::log('create_witness', 'witness_report', $witness->id);

        // Upload file bukti jika ada, termasuk SHA-256 hash
        if ($request->hasFile('evidence_file')) {
            $file = $request->file('evidence_file');
            $path = $file->store('evidences/witness', 'public');
            $hash = hash_file('sha256', $file->getRealPath());

            WitnessEvidence::create([
                'witness_report_id' => $witness->id,
                'file_url'          => $path,
                'file_type'         => $file->getClientMimeType(),
                'file_hash'         => $hash,
                'uploaded_at'       => now(),
            ]);
        }

        if ($fromDashboard) {
            return redirect()->route('dashboard')->with('witness_success', 'Terima kasih! Bukti kesaksianmu berhasil disimpan.');
        }

        return back()->with('success', 'Terima kasih! Bukti kesaksianmu berhasil disimpan dan akan membantu korban.');
    }
}
