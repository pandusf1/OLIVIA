<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Tampilkan halaman reset kata sandi.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $token = $request->route('token');

        if (!$token || !Cache::has('password_reset_token:' . $token)) {
            return redirect()->route('password.request')
                ->with('error', 'Token lupa sandi tidak valid atau telah kedaluwarsa.');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Tangani permintaan kata sandi baru yang masuk.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $token = $request->input('token');
        $userId = Cache::get('password_reset_token:' . $token);

        if (!$userId) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi ganti kata sandi tidak valid atau telah kedaluwarsa.');
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('password.request')
                ->with('error', 'Pengguna tidak ditemukan.');
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        Cache::forget('password_reset_token:' . $token);

        return redirect()->route('login')->with('status', 'Kata sandi Anda berhasil diperbarui. Silakan masuk.');
    }
}
