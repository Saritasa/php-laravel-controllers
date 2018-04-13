<?php

namespace Saritasa\Laravel\Controllers\Providers;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Contracts\ControllerDispatcher as LaravelControllerDispatcher;
use Illuminate\Support\ServiceProvider;
use Saritasa\Laravel\Controllers\ControllerDispatcher;
use Saritasa\Laravel\Controllers\Router;

class ControllersServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot(): void
    {
        $this->replacesDefaultLaravelResolvers();
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'controllers');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'controllers');
    }

    public function register()
    {
        $this->app->register(RevisionsServiceProvider::class);
    }

    /**
     * Replaces laravel cores classes to custom realization.
     */
    protected function replacesDefaultLaravelResolvers(): void
    {
        $this->app->bind(Registrar::class, Router::class);
        $this->app->bind(LaravelControllerDispatcher::class, ControllerDispatcher::class);
    }
}
