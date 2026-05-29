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

        if ($user && $user->role === 'user') {
            $allowedRoutes = [
                'settings',
                'settings.update',
                'profile.update',
                'profile.edit',
                'profile.phone.verify',
                'profile.phone.remove',
                'logout',
            ];

            if (empty($user->phone)) {
                if ($request->routeIs(...$allowedRoutes)) {
                    return $next($request);
                }

                return redirect()->route('settings')->with('warning', 'Anda harus mengisi nomor WhatsApp terlebih dahulu sebelum menggunakan fitur lainnya.');
            }

            if (!$user->phone_is_verified) {
                if ($request->routeIs(...$allowedRoutes)) {
                    return $next($request);
                }

                return redirect()->route('settings')
                    ->with('verify_user_phone', $user->phone)
                    ->with('warning', 'Verifikasi nomor WhatsApp terlebih dahulu sebelum menggunakan fitur lainnya.');
            }

            if ($request->routeIs(...$allowedRoutes)) {
                return $next($request);
            }
        }

        return $next($request);
    }
}
