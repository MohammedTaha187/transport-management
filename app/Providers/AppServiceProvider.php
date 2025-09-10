<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Providers\FilamentCustomServiceProvider;
use App\Models\Trip;
use App\Observers\TripObserver;

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
        // Register Filament custom provider if Filament is available
        if (class_exists(FilamentCustomServiceProvider::class)) {
            $this->app->register(FilamentCustomServiceProvider::class);
        }

        // Register model observers
        Trip::observe(TripObserver::class);
    }
}
