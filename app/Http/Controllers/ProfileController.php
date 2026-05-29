<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\FonnteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldPhone = $user->phone;

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $phoneChanged = $oldPhone !== $user->phone;

        if ($phoneChanged) {
            $code = str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $user->phone_is_verified = false;
            $user->phone_verification_code = $code;
        }

        $user->save();

        if ($phoneChanged) {
            $message = "Halo {$user->name},\n\nNomor WhatsApp ini baru saja dimasukkan di akun Safora Anda.\nKode verifikasi Anda adalah: *{$code}*\n\nJika Anda tidak melakukan perubahan ini, abaikan pesan ini.";
            FonnteService::send($user->phone, $message);

            return Redirect::route('settings')
                ->with('verify_user_phone', $user->phone)
                ->with('success', 'Nomor WhatsApp diubah. Silakan masukkan kode verifikasi yang dikirim ke WhatsApp.');
        }

        return Redirect::route('settings')->with('success', 'Profil berhasil diperbarui.');

    }

    public function verifyPhone(Request $request): RedirectResponse
    {
        $user = $request->user();
        $code = (string) $request->input('code', '');

        if (strlen($code) !== 5) {
            return Redirect::route('settings')
                ->with('verify_user_phone', $user->phone)
                ->with('error', 'Kode verifikasi harus 5 digit.');
        }

        if ($user->phone_verification_code === $code) {
            $user->update([
                'phone_is_verified' => true,
                'phone_verification_code' => null,
            ]);

            return Redirect::route('settings')->with('success', 'Nomor WhatsApp berhasil diverifikasi.');
        }

        return Redirect::route('settings')
            ->with('verify_user_phone', $user->phone)
            ->with('error', 'Kode verifikasi salah.');
    }

    public function resendPhoneVerification(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->phone) {
            return Redirect::route('settings')->with('error', 'Nomor WhatsApp belum diisi.');
        }

        if ($user->phone_is_verified) {
            return Redirect::route('settings')->with('success', 'Nomor WhatsApp sudah terverifikasi.');
        }

        $code = str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $user->update([
            'phone_verification_code' => $code,
        ]);

        $message = "Halo {$user->name},\n\nKode verifikasi WhatsApp Safora Anda adalah: *{$code}*\n\nJika Anda tidak meminta kode ini, abaikan pesan ini.";
        FonnteService::send($user->phone, $message);

        return Redirect::route('settings')
            ->with('verify_user_phone', $user->phone)
            ->with('success', 'Kode verifikasi baru sudah dikirim ke WhatsApp.');
    }

    public function removePhone(Request $request): RedirectResponse
    {
        $request->user()->update([
            'phone' => null,
            'phone_is_verified' => false,
            'phone_verification_code' => null,
        ]);

        return Redirect::route('settings')->with('warning', 'Nomor WhatsApp dibatalkan. Silakan masukkan nomor lagi untuk melanjutkan.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
