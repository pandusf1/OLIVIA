<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminPartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::latest()->get();

        return view('pages.admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('pages.admin.partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_name' => 'required|string|max:255',
            'partner_type' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',

            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $partner = Partner::create([
            'partner_name' => $request->partner_name,
            'partner_type' => $request->partner_type,
            'city' => $request->city,
            'phone' => $request->phone,
            'email' => $request->email,
            'verified' => true,
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'partner',
            'partner_id' => $partner->id,
        ]);

        return redirect()
            ->route('admin.partners')
            ->with('success', 'Partner berhasil ditambahkan.');
    }

    public function toggleVerify($id)
    {
        $partner = Partner::findOrFail($id);

        $partner->verified = !$partner->verified;
        $partner->save();

        return back()->with(
            'success',
            $partner->verified
                ? 'Partner berhasil diverifikasi.'
                : 'Verifikasi partner dicabut.'
        );
    }
}