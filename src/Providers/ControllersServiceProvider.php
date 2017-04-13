<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Illuminate\Support\ServiceProvider;

class ControllersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', '');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', '');
    }

    public function register()
    {
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(RevisionsServiceProvider::class);
    }
}