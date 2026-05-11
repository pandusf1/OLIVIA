<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\AuditLog;

class PartnerController extends Controller
{
    public function index()
    {
        $reports = Report::withCount('evidences')->latest()->get();

        $stats = [
            'submitted' => Report::where('status', 'Submitted')->count(),
            'progress' => Report::where('status', 'In Progress')->count(),
            'resolved' => Report::where('status', 'Resolved')->count(),
        ];

        return view('pages.partner.index', compact('reports', 'stats'));
    }

    public function show($id)
    {
        $report = Report::with([
            'evidences',
            'statusLogs',
            'witnessReports.evidences',
        ])->findOrFail($id);

        // Catat bahwa mitra telah melihat laporan
        if ($report->status === 'Routed') {
            $old = $report->status;
            $report->status = 'Viewed';
            $report->save();

            ReportStatusLog::create([
                'report_id'  => $report->id,
                'old_status' => $old,
                'new_status' => 'Viewed',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            AuditLog::log('view_report', 'report', $report->id);
        }

        return view('pages.partner.show', compact('report'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string|in:Submitted,Routed,Viewed,In Progress,Resolved']);

        $report = Report::findOrFail($id);
        $oldStatus = $report->status;

        $report->status = $request->status;
        $report->save();

        ReportStatusLog::create([
            'report_id'  => $report->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        AuditLog::log('update_status', 'report', $report->id);

        return back()->with('success', 'Status laporan diperbarui ke "' . $request->status . '".');
    }
    
}
