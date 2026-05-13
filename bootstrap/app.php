<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Tambahkan alias middleware kamu di sini
        $middleware->alias([
            'partner' => \App\Http\Middleware\EnsurePartnerRole::class,
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,

        ]);

        // 2. Tambahkan TrustProxies di sini agar HTTPS terdeteksi benar di Vercel
        $middleware->trustProxies(at: '*'); 
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();