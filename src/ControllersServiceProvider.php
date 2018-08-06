<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Contracts\ControllerDispatcher as LaravelControllerDispatcher;
use Illuminate\Support\ServiceProvider;

/**
 * Controllers service provider.
 */
class ControllersServiceProvider extends ServiceProvider
{
    /**
     * Provider boot actions.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->replacesDefaultLaravelResolvers();
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'controllers');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'controllers');
    }

    /**
     * Replaces laravel cores classes to custom implementation.
     *
     * @return void
     */
    protected function replacesDefaultLaravelResolvers(): void
    {
        $this->app->bind(Registrar::class, Router::class);
        $this->app->bind(LaravelControllerDispatcher::class, ControllerDispatcher::class);
    }
}
