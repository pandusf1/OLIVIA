<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrustedContact;

class TrustedContactController extends Controller
{
    public function index()
    {
        $contacts = auth()->user()->trustedContacts;
        return view('trusted-contacts.index', compact('contacts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contact_name'  => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
        ]);

        TrustedContact::create([
            'user_id'       => auth()->id(),
            'contact_name'  => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'created_at'    => now(),
        ]);

        return back()->with('success', 'Kontak terpercaya berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        TrustedContact::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail()
            ->delete();

        return back()->with('success', 'Kontak dihapus.');
    }
}
