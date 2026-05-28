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

        $existing = TrustedContact::where('user_id', auth()->id())
            ->where('contact_phone', $request->contact_phone)
            ->first();

        if ($existing) {
            return back()->with('error', 'Nomor ini sudah ada di daftar kontak terpercaya Anda.');
        }

        $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);

        $contact = TrustedContact::create([
            'user_id'       => auth()->id(),
            'contact_name'  => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'is_verified'   => false,
            'verification_code' => $code,
            'created_at'    => now(),
        ]);

        $message = "Halo {$contact->contact_name},\n\nAnda didaftarkan sebagai kontak terpercaya oleh " . auth()->user()->name . " di aplikasi Safora.\nKode verifikasi Anda adalah: *{$code}*\n\nJika Anda tidak mengenali permintaan ini, abaikan pesan ini.";
        \App\Services\FonnteService::send($contact->contact_phone, $message);

        return back()->with('verify_contact_id', $contact->id)
                     ->with('verify_contact_phone', $contact->contact_phone)
                     ->with('success', 'Silakan masukkan kode verifikasi yang dikirim ke WhatsApp.');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'contact_id' => 'required',
            'code' => 'required|string|size:5',
        ]);

        $contact = TrustedContact::where('id', $request->contact_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($contact->verification_code === $request->code) {
            $contact->update([
                'is_verified' => true,
                'verification_code' => null,
            ]);
            return redirect()->route('trusted-contact.index')->with('success', 'Kontak terpercaya berhasil diverifikasi.');
        }

        return back()->with('verify_contact_id', $contact->id)
                     ->with('verify_contact_phone', $contact->contact_phone)
                     ->with('error', 'Kode verifikasi salah.');
    }

    public function edit($id)
    {
        $contact = TrustedContact::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return view('trusted-contacts.edit', compact('contact'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'contact_name'  => 'required|string|max:100',
            'contact_phone' => 'required|string|max:20',
        ]);

        $contact = TrustedContact::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $needsVerification = $contact->contact_phone !== $request->contact_phone;

        if ($needsVerification) {
            $existing = TrustedContact::where('user_id', auth()->id())
                ->where('contact_phone', $request->contact_phone)
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return back()->with('error', 'Nomor ini sudah ada di daftar kontak terpercaya Anda.');
            }
        }

        $updateData = [
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
        ];

        if ($needsVerification) {
            $code = str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $updateData['is_verified'] = false;
            $updateData['verification_code'] = $code;
        }

        $contact->update($updateData);

        if ($needsVerification) {
            $message = "Halo {$contact->contact_name},\n\nNomor Anda telah diperbarui sebagai kontak terpercaya oleh " . auth()->user()->name . " di aplikasi Safora.\nKode verifikasi Anda adalah: *{$code}*\n\nJika Anda tidak mengenali permintaan ini, abaikan pesan ini.";
            \App\Services\FonnteService::send($contact->contact_phone, $message);

            return redirect()->route('trusted-contact.index')
                         ->with('verify_contact_id', $contact->id)
                         ->with('verify_contact_phone', $contact->contact_phone)
                         ->with('success', 'Nomor telepon diubah. Silakan masukkan kode verifikasi baru.');
        }

        return redirect()->route('trusted-contact.index')->with('success', 'Kontak berhasil diperbarui.');
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
