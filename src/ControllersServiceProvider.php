<?php

namespace Saritasa\Laravel\Controllers;

use Illuminate\Support\ServiceProvider;

class ControllersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', '');
        $this->loadViewsFrom(__DIR__.'/../resources/views', '');
    }
}