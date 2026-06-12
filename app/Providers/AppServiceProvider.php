<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan service aplikasi apa pun.
     */
public function register(): void
{
    // Tambahkan ini agar Laravel mengenali folder public di Vercel
    $this->app->bind('path.public', function() {
        return base_path('public');
    });
}

    /**
     * Bootstrap service aplikasi apa pun.
     */
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }

    // Paksa pembuat URL untuk menggunakan skema dan host permintaan saat ini jika berupa permintaan web
    if (!app()->runningInConsole() && request()->getSchemeAndHttpHost()) {
        \Illuminate\Support\Facades\URL::forceRootUrl(request()->getSchemeAndHttpHost());
    }

    \Illuminate\Database\Connection::resolverFor('pgsql', function ($connection, $database, $prefix, $config) {
        return new \App\Database\Connectors\CustomPostgresConnection($connection, $database, $prefix, $config);
    });
}
}
