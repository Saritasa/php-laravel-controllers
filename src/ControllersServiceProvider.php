<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelControllers\Requests\Concerns\ILoginRequest;
use Saritasa\LaravelControllers\Requests\LoginRequest;

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
        $this->app->bindIf(ILoginRequest::class, LoginRequest::class);
    }
}
