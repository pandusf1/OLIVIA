<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsFilled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'user' && empty($user->phone)) {
            // Biarkan lewat jika mengakses halaman settings atau profile.update atau logout
            if ($request->routeIs('settings') || $request->routeIs('settings.update') || $request->routeIs('profile.update') || $request->routeIs('profile.edit') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('settings')->with('warning', 'Anda harus mengisi nomor telepon terlebih dahulu sebelum menggunakan fitur lainnya.');
        }

        return $next($request);
    }
}
