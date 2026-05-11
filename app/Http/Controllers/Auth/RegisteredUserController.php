<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Partner;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'in:user,partner'],
            'partner_name' => ['nullable', 'string', 'max:255'],
            'partner_type' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone ?: null,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->role === 'partner') {
            Partner::create([
                'partner_name' => $request->partner_name ?: $request->name,
                'partner_type' => $request->partner_type,
                'city' => $request->city,
                'phone' => $request->phone,
                'email' => $request->email,
                'verified' => false,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        AuditLog::log('register', 'user', $user->id);

        return redirect($user->role === 'partner' ? route('partner.index') : route('dashboard'));
    }
}
