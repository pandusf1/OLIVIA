<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class SettingsController extends Controller
{
    public function index()
    {
        $reportCount = Report::where('user_id', auth()->id())->count();
        return view('settings.index', compact('reportCount'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        if ($request->has('receive_nearby_alerts')) {
            $user->receive_nearby_alerts = $request->boolean('receive_nearby_alerts');
            $user->save();
            return back()->with('success', 'Pengaturan notifikasi berhasil diperbarui.');
        }

        return back();
    }
}
