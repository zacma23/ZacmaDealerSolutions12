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
        if ($this->app->environment('production') || !empty($_ENV['VERCEL']) || !empty($_SERVER['VERCEL'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Use SingleCookieSessionHandler for stateless, bounded cookie sessions on serverless
        $this->app->make('session')->extend('cookie', function ($app) {
            return new \App\Services\Session\SingleCookieSessionHandler(
                $app->make('cookie'),
                (int) (config('session.lifetime') ?: 120),
                (bool) config('session.expire_on_close', false),
                'zacma_session_data'
            );
        });
    }
}
