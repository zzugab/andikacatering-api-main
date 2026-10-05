<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

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
        Response::macro('success', function ($data = [], $message = '', $settings = []) {
            return Response::make([
                'data' => $data,
                'message' => $message,
                'settings' => $settings
            ], 200);
        });

        Response::macro('failed', function ($error = [], $httpCode = 422, $settings = []) {
            return Response::make([
                'errors' => $error,
                'settings' => $settings
            ], $httpCode);
        });
    }
}
