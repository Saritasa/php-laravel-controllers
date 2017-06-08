<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Illuminate\Support\ServiceProvider;

class ControllersServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'controllers');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'controllers');
    }

    public function register()
    {
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(RevisionsServiceProvider::class);
    }
}