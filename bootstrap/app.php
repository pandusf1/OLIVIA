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
            'mitra' => \App\Http\Middleware\EnsureMitraRole::class,
            'admin' => \App\Http\Middleware\EnsureAdminRole::class,
            'phone.required' => \App\Http\Middleware\EnsurePhoneIsFilled::class,
        ]);

        // 2. Tambahkan TrustProxies di sini agar HTTPS terdeteksi benar di Vercel
        $middleware->trustProxies(at: '*'); 
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()
                || $e instanceof \Illuminate\Validation\ValidationException
                || $e instanceof \Illuminate\Auth\AuthenticationException
                || $e instanceof \Illuminate\Auth\Access\AuthorizationException
                || $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                || $e instanceof \Illuminate\Session\TokenMismatchException
                || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface
            ) {
                return null;
            }

            \Illuminate\Support\Facades\Log::error('System error shown to user', [
                'message' => $e->getMessage(),
                'url' => $request->fullUrl(),
                'user_id' => optional($request->user())->id,
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Maaf, ada masalah di sistem. Silakan coba lagi dari halaman ini atau kembali ke halaman sebelumnya.')
                ->with('system_error', 'Maaf, ada masalah di sistem. Silakan coba lagi dari halaman ini atau kembali ke halaman sebelumnya.');
        });
    })->create();
