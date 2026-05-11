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
}
