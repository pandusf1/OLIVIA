<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ChatThread;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'mitra') {
            return redirect()->route('mitra.index');
        }

        $userHasLocation = \App\Models\UserLocation::where('user_id', auth()->id())->exists();

        $reports = collect();
        $totalReports = 0;
        $activeReports = 0;
        $resolvedReports = 0;
        $totalEvidences = 0;
        $chatThreads = collect();

        return view('dashboard', compact(
            'reports', 'totalReports', 'activeReports', 'resolvedReports', 'totalEvidences',
            'chatThreads', 'userHasLocation'
        ));
    }

    public function summaryData()
    {
        $reports = Report::where('user_id', auth()->id())
            ->with(['mitra'])
            ->withCount('evidences')
            ->latest()
            ->get();

        $totalReports    = $reports->count();
        $activeReports   = $reports->whereIn('status', ['Submitted', 'Routed', 'Viewed', 'In Progress'])->count();
        $resolvedReports = $reports->where('status', 'Resolved')->count();
        $totalEvidences  = $reports->sum('evidences_count');

        $chatThreads = ChatThread::query()
            ->where('user_id', auth()->id())
            ->with('mitra')
            ->orderByDesc('last_message_at')
            ->get();

        $sc = [
            'Submitted'   => ['bg'=>'bg-gray-100',    'text'=>'text-gray-600',  'dot'=>'bg-gray-400'],
            'Routed'      => ['bg'=>'bg-blue-50',     'text'=>'text-blue-700',  'dot'=>'bg-blue-500'],
            'Viewed'      => ['bg'=>'bg-yellow-50',   'text'=>'text-yellow-700','dot'=>'bg-yellow-500'],
            'In Progress' => ['bg'=>'bg-orange-50',   'text'=>'text-orange-700','dot'=>'bg-orange-500'],
            'Resolved'    => ['bg'=>'bg-green-50',    'text'=>'text-green-700', 'dot'=>'bg-green-500'],
        ];

        return response()->json([
            'totalReports' => $totalReports,
            'activeReports' => $activeReports,
            'resolvedReports' => $resolvedReports,
            'totalEvidences' => $totalEvidences,
            'reports' => $reports->take(7)->map(function ($r) use ($sc) {
                $s = $sc[$r->status] ?? $sc['Submitted'];
                $catMap = [
                    'ambulance' => 'Medis Darurat',
                    'legal' => 'Bantuan Hukum',
                    'counselor' => 'Psikososial',
                    'pemadam' => 'Pemadam / Rescue',
                    'pppa' => 'Layanan PPPA'
                ];
                $catLabel = $catMap[strtolower($r->category)] ?? $r->category;
                return [
                    'id' => $r->id,
                    'category' => $catLabel,
                    'status' => $r->status,
                    'status_label' => $r->status_label_indonesian,
                    'anonymous' => (bool) $r->anonymous,
                    'evidences_count' => $r->evidences_count,
                    'incident_date' => $r->incident_date 
                    ? \Carbon\Carbon::parse($r->incident_date)->format('d M Y, H:i') 
                    : $r->created_at->format('d M Y, H:i'),
                    'status_classes' => $s,
                    'is_editable_deletable' => in_array($r->status, ['Submitted', 'Routed', 'Viewed']) 
                        && $r->created_at->diffInMinutes(now()) <= 15,
                    'created_at_timestamp' => $r->created_at->timestamp,
                    'description' => $r->description,
                ];
            }),
            'chatThreads' => $chatThreads->map(function ($t) {
                return [
                    'id' => $t->id,
                    'mitra_id' => $t->mitra_id,
                    'mitra_name' => $t->mitra?->mitra_name ?? 'Mitra',
                    'last_message' => $t->last_message,
                    'last_message_at' => $t->last_message_at 
                        ? \Carbon\Carbon::parse($t->last_message_at)->diffForHumans() 
                        : null,
                ];
            }),
        ]);
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
