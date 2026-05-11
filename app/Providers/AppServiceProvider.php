<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
    public function boot(): void
    {
        //
    }
}
