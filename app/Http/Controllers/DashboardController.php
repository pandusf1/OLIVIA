<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'partner') {
            return redirect()->route('partner.index');
        }

        $reports = Report::where('user_id', auth()->id())
            ->withCount('evidences')
            ->latest()
            ->get();

        $totalReports    = $reports->count();
        $activeReports   = $reports->whereIn('status', ['Submitted', 'Routed', 'Viewed', 'In Progress'])->count();
        $resolvedReports = $reports->where('status', 'Resolved')->count();
        $totalEvidences  = $reports->sum('evidences_count');

        return view('dashboard', compact('reports', 'totalReports', 'activeReports', 'resolvedReports', 'totalEvidences'));
    }
}
