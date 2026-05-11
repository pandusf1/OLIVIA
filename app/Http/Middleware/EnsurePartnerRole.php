<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePartnerRole
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->isPartner()) {
            abort(403, 'Akses hanya untuk mitra terverifikasi.');
        }

        return $next($request);
    }
}
