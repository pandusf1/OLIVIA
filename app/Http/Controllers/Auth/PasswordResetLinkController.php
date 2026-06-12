<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FonnteService;
use App\Services\PhoneNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Tampilkan halaman permintaan tautan reset kata sandi.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Tangani permintaan masuk untuk tautan reset kata sandi.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'identity' => ['required', 'string'],
        ], [
            'identity.required' => 'Email atau Nomor WhatsApp wajib diisi.',
        ]);

        $identity = $request->input('identity');
        $normalizedPhone = PhoneNumberService::normalize($identity);

        $user = User::where('email', $identity)
            ->orWhere(function ($query) use ($normalizedPhone) {
                if ($normalizedPhone) {
                    $query->where('phone', $normalizedPhone);
                } else {
                    $query->whereRaw('1=0');
                }
            })
            ->first();

        if (!$user) {
            return back()->withInput()->withErrors(['identity' => 'Akun dengan Email atau Nomor WhatsApp tersebut tidak ditemukan.']);
        }

        if (empty($user->phone)) {
            return back()->withInput()->withErrors(['identity' => 'Akun Anda tidak memiliki nomor WhatsApp yang terdaftar. Silakan hubungi admin.']);
        }

        $cooldownKey = 'password_reset_otp_cooldown:' . $user->id;
        $remainingSeconds = 0;
        if (Cache::has($cooldownKey)) {
            $availableAt = Cache::get($cooldownKey);
            $remainingSeconds = max(0, $availableAt - time());
        }

        if ($remainingSeconds > 0) {
            session(['password_reset_user_id' => $user->id]);
            return redirect()->route('password.otp-verify-view')
                ->with('phone_resend_seconds', $remainingSeconds)
                ->with('error', 'Tunggu ' . $remainingSeconds . ' detik sebelum mengirim ulang kode.');
        }

        $otpCode = str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);

        Cache::put('password_reset_otp:' . $user->id, $otpCode, now()->addMinutes(10));
        Cache::put($cooldownKey, time() + 60, now()->addSeconds(60));

        $message = "Halo {$user->name},\n\nKode OTP lupa sandi Safora Anda adalah: *{$otpCode}*\n\nKode ini berlaku selama 10 menit. Jika Anda tidak meminta kode ini, abaikan pesan ini.";
        FonnteService::send($user->phone, $message);

        session(['password_reset_user_id' => $user->id]);

        return redirect()->route('password.otp-verify-view')
            ->with('phone_resend_seconds', 60)
            ->with('success', 'Kode OTP telah dikirim ke nomor WhatsApp Anda.');
    }

    /**
     * Tampilkan halaman verifikasi OTP.
     */
    public function verifyOtpView(Request $request): View|RedirectResponse
    {
        $userId = session('password_reset_user_id');

        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'Silakan masukkan email atau nomor WhatsApp Anda terlebih dahulu.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $phone = $user->phone;
        $maskedPhone = '';
        if ($phone) {
            $len = strlen($phone);
            if ($len > 7) {
                $maskedPhone = substr($phone, 0, 5) . str_repeat('*', $len - 8) . substr($phone, -3);
            } else {
                $maskedPhone = substr($phone, 0, 3) . str_repeat('*', $len - 3);
            }
        }

        return view('auth.forgot-password-otp', [
            'maskedPhone' => $maskedPhone,
            'userId' => $user->id,
        ]);
    }

    /**
     * Verifikasi input kode OTP.
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');

        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi telah kedaluwarsa. Silakan masukkan email atau nomor WhatsApp Anda kembali.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:5'],
        ], [
            'code.required' => 'Kode OTP harus diisi.',
            'code.size' => 'Kode OTP harus terdiri dari 5 digit.',
        ]);

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $cachedOtp = Cache::get('password_reset_otp:' . $user->id);

        if (!$cachedOtp || $cachedOtp !== $request->input('code')) {
            return back()->withErrors(['code' => 'Kode OTP salah atau telah kedaluwarsa.']);
        }

        Cache::forget('password_reset_otp:' . $user->id);
        session()->forget('password_reset_user_id');

        $token = Str::random(40);
        Cache::put('password_reset_token:' . $token, $user->id, now()->addMinutes(10));

        return redirect()->route('password.reset', ['token' => $token])
            ->with('success', 'Verifikasi berhasil. Silakan buat kata sandi baru.');
    }

    /**
     * Kirim ulang kode OTP.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userId = session('password_reset_user_id');

        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi telah kedaluwarsa. Silakan masukkan email atau nomor WhatsApp Anda kembali.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $cooldownKey = 'password_reset_otp_cooldown:' . $user->id;
        if (Cache::has($cooldownKey)) {
            $availableAt = Cache::get($cooldownKey);
            $remainingSeconds = max(0, $availableAt - time());
            if ($remainingSeconds > 0) {
                return back()
                    ->with('phone_resend_seconds', $remainingSeconds)
                    ->with('error', 'Tunggu ' . $remainingSeconds . ' detik sebelum mengirim ulang kode.');
            }
        }

        $otpCode = str_pad((string) rand(0, 99999), 5, '0', STR_PAD_LEFT);
        Cache::put('password_reset_otp:' . $user->id, $otpCode, now()->addMinutes(10));
        Cache::put($cooldownKey, time() + 60, now()->addSeconds(60));

        $message = "Halo {$user->name},\n\nKode OTP lupa sandi Safora Anda yang baru adalah: *{$otpCode}*\n\nKode ini berlaku selama 10 menit. Jika Anda tidak meminta kode ini, abaikan pesan ini.";
        FonnteService::send($user->phone, $message);

        return back()
            ->with('phone_resend_seconds', 60)
            ->with('success', 'Kode OTP baru telah dikirim ke WhatsApp.');
    }
}
