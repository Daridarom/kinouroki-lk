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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Статичная выгрузка для GitHub Pages (scripts/export-static.py): все ссылки с префиксом сайта
        if ($root = env('KA_STATIC_ROOT')) {
            \Illuminate\Support\Facades\URL::forceRootUrl($root);
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        \Illuminate\Support\Carbon::setLocale('ru');
        //
    }
}
