<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show($id)
    {
        $report = Report::with([
            'evidences',
            'statusLogs',
            'partner',
            'witnessReports.evidences',
        ])->findOrFail($id);

        return view('pages.tracking', compact('report'));
    }

    public function search(Request $request)
    {
        if ($request->has('id') && $request->id) {
            $report = Report::find($request->id);
            if ($report) {
                return redirect('/tracking/' . $report->id);
            }
            return back()->with('error', 'Laporan dengan ID tersebut tidak ditemukan.');
        }
        return view('pages.tracking_search');
    }
}
