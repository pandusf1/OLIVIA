<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Partner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'reports' => Report::count(),
            'emergency' => Report::where('report_type', 'Emergency')->count(),
            'resolved' => Report::where('status', 'Resolved')->count(),
            'partners' => Partner::count(),
            'users' => User::count(),
        ];

        $reports = Report::latest()->take(10)->get();

        return view('pages.admin.index', compact('stats', 'reports'));
    }

    public function partners()
    {
        $partners = Partner::latest()->get();

        return view('pages.admin.partners.index', compact('partners'));
    }

    public function createPartner()
{
    return view('pages.admin.partners.create');
}

public function storePartner(Request $request)
{
    $request->validate([
        'partner_name' => 'required|string|max:255',
        'partner_type' => 'required|string|max:255',
        'city' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'nullable|string|max:30',
    ]);

    $partner = Partner::create([
        'partner_name' => $request->partner_name,
        'partner_type' => $request->partner_type,
        'city' => $request->city,
        'phone' => $request->phone,
        'email' => $request->email,
        'verified' => true,
    ]);

    $password = Str::random(10);

    User::create([
        'name' => $request->partner_name,
        'email' => $request->email,
        'password' => Hash::make($password),
        'role' => 'partner',
        'partner_id' => $partner->id,
    ]);

    return redirect()
        ->route('admin.partners')
        ->with('success', 'Partner berhasil dibuat. Password: ' . $password);
}

public function verifyPartner($id)
{
    $partner = Partner::findOrFail($id);

    $partner->verified = !$partner->verified;

    $partner->save();

    return back()->with('success', 'Status partner diperbarui.');
}
}