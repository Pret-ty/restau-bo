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
        // Configure essa/api-tool-kit exception handler
        $this->app->bind(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            \Essa\APIToolKit\Exceptions\Handler::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }


}
