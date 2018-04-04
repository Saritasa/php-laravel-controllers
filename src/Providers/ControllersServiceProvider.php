<?php

namespace Saritasa\Laravel\Controllers\Providers;

use App\ControllerDispatcher;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Routing\Contracts\ControllerDispatcher as LaravelControllerDispatcher;
use Illuminate\Support\ServiceProvider;
use Saritasa\Laravel\Controllers\Router;

class ControllersServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->updateDefaultLaravelResolvers();
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'controllers');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'controllers');
    }

    public function register()
    {
        $this->app->register(MacroServiceProvider::class);
        $this->app->register(RevisionsServiceProvider::class);
    }

    protected function updateDefaultLaravelResolvers()
    {
        $this->app->bind(Registrar::class, Router::class);
        $this->app->bind(LaravelControllerDispatcher::class, ControllerDispatcher::class);
    }
}
