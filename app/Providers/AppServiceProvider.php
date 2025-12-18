<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        // Força https na geração de URLs/Assets em produção (evita Mixed Content
        // quando o TLS termina no Traefik e a app recebe HTTP internamente).
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
