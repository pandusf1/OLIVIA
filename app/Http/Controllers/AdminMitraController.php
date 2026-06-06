<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mitra;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminMitraController extends Controller
{
    public function index()
    {
        $mitras = Mitra::latest()->get();

        return view('pages.admin.mitras.index', compact('mitras'));
    }

    public function create()
    {
        return view('pages.admin.mitras.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mitra_name' => 'required|string|max:255',
            'mitra_type' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string',

            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $mitra = Mitra::create([
            'mitra_name' => $request->mitra_name,
            'mitra_type' => $request->mitra_type,
            'city' => $request->city,
            'phone' => $request->phone,
            'email' => $request->email,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'verified' => true,
            'is_active' => true,
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'mitra',
            'mitra_id' => $mitra->id,
        ]);

        return redirect()
            ->route('admin.mitras')
            ->with('success', 'Mitra berhasil ditambahkan.');
    }

    public function toggleVerify($id)
    {
        $mitra = Mitra::findOrFail($id);

        $mitra->verified = !$mitra->verified;
        $mitra->save();

        return back()->with(
            'success',
            $mitra->verified
                ? 'Mitra berhasil diverifikasi.'
                : 'Verifikasi mitra dicabut.'
        );
    }

    public function toggleActive($id)
    {
        $mitra = Mitra::findOrFail($id);

        $mitra->is_active = !$mitra->is_active;
        $mitra->save();

        AuditLog::log('toggle_mitra_active', 'mitra', $mitra->id);

        return back()->with(
            'success',
            $mitra->is_active
                ? 'Mitra berhasil diaktifkan.'
                : 'Mitra berhasil dinonaktifkan.'
        );
    }
}
