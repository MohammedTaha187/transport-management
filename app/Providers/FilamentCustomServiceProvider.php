<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;
use App\Models\Trip;

class FilamentCustomServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if (! class_exists(Filament::class)) {
            return;
        }

        // Topbar injection removed: active trips badge intentionally disabled.

        // Register custom CSS by injecting a link tag into the head
        Filament::registerRenderHook('panels::head.start', function () {
            return view('filament.head.overrides');
        });
    }
}
