<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ChatThread;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'partner') {
            return redirect()->route('partner.index');
        }

        $reports = Report::where('user_id', auth()->id())
            ->with(['partner'])
            ->withCount('evidences')
            ->latest()
            ->get();

        $totalReports    = $reports->count();
        $activeReports   = $reports->whereIn('status', ['Submitted', 'Routed', 'Viewed', 'In Progress'])->count();
        $resolvedReports = $reports->where('status', 'Resolved')->count();
        $totalEvidences  = $reports->sum('evidences_count');

        $chatThreads = ChatThread::query()
            ->where('user_id', auth()->id())
            ->with('partner')
            ->orderByDesc('last_message_at')
            ->get();

        $userHasLocation = \App\Models\UserLocation::where('user_id', auth()->id())->exists();

        return view('dashboard', compact(
            'reports', 'totalReports', 'activeReports', 'resolvedReports', 'totalEvidences',
            'chatThreads', 'userHasLocation'
        ));
    }

    public function reportsJson(Request $request)
    {
        $query = Report::where('user_id', auth()->id())
            ->withCount('evidences');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $reports = $query->latest()->paginate(15);

        return response()->json($reports);
    }
}
