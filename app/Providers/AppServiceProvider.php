<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        //
        Model::unguard();

        Model::preventLazyLoading();

        Password::defaults(function () {
            return $this->app->isProduction()
                ? Password::min(8)->max(16)->symbols()->mixedCase()->uncompromised(1)
                : Password::min(8)->max(16)->symbols()->mixedCase();
        });


    }
}
