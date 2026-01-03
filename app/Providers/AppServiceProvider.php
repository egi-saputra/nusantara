<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia; // <- jangan lupa import ini

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bagikan data auth ke semua halaman Inertia
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->user(),
                    'role' => auth()->user()?->role, // misal 'proktor', 'guru', 'siswa'
                ];
            },
        ]);
    }
}
