<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    $user = Auth::user();

    AuditLog::log('login', 'user', $user->id);

    // ADMIN
    if ($user->role === 'admin') {
        return redirect()->intended(route('admin.index'));
    }

    // MITRA
    if ($user->isMitra()) {
        return redirect()->intended(route('mitra.index'));
    }

    // USER BIASA
    return redirect()->intended(route('dashboard'));
}

    public function destroy(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        if ($userId) {
            AuditLog::log('logout', 'user', $userId);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
