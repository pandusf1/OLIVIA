<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Mitra;
use App\Services\FonnteService;
use App\Services\PhoneNumberService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'phone' => PhoneNumberService::normalize($request->input('phone')),
        ]);

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^62[0-9]{8,18}$/', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'phone.regex' => 'Nomor WhatsApp harus berupa nomor Indonesia dengan format 62...',
            'phone.unique' => 'Nomor WhatsApp ini sudah digunakan oleh akun lain.',
        ]);

        $code = $request->phone ? str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT) : null;

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone ?: null,
            'phone_is_verified' => $request->phone ? false : true,
            'phone_verification_code' => $code,
            'password' => Hash::make($request->password),

            // semua register publik = user biasa
            'role' => 'user',
        ]);

        event(new Registered($user));

        Auth::login($user);

        AuditLog::log('register', 'user', $user->id);

        if ($request->phone) {
            $message = "Halo {$user->name},\n\nNomor WhatsApp ini baru saja didaftarkan di akun Safora Anda.\nKode verifikasi Anda adalah: *{$code}*\n\nJika Anda tidak melakukan pendaftaran ini, abaikan pesan ini.";
            FonnteService::send($user->phone, $message);
            Cache::put('phone_verification_resend_available_at:' . $user->id, time() + 60, now()->addSeconds(60));

            return redirect(route('settings'))
                ->with('verify_user_phone', $user->phone)
                ->with('phone_resend_seconds', 60)
                ->with('success', 'Akun berhasil dibuat. Silakan masukkan kode verifikasi yang dikirim ke WhatsApp.');
        }

        return redirect(route('dashboard'));
    }
}
