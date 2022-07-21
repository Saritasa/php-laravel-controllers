<?php

namespace Saritasa\LaravelControllers;

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
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'controllers');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'controllers');
    }
}
