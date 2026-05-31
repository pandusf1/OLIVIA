<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
public function register(): void
{
    // Tambahkan ini agar Laravel mengenali folder public di Vercel
    $this->app->bind('path.public', function() {
        return base_path('public');
    });
}

    /**
     * Bootstrap any application services.
     */
// app/Providers/AppServiceProvider.php

public function boot(): void
{
    if (config('app.env') === 'production') {
        \Illuminate\Support\Facades\URL::forceScheme('https');
    }

    // Force URL generator to use the current request's scheme and host if it is a web request
    if (!app()->runningInConsole() && request()->getSchemeAndHttpHost()) {
        \Illuminate\Support\Facades\URL::forceRootUrl(request()->getSchemeAndHttpHost());
    }
}
}
