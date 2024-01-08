<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Support\ServiceProvider;
use Saritasa\LaravelControllers\Api\BaseApiController;
use Saritasa\LaravelControllers\Requests\Concerns\ILoginRequest;
use Saritasa\Transformers\IDataTransformer;

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
        $this->app->bindIf(IDataTransformer::class, BaseApiController::class);
        $this->app->bindIf(ILoginRequest::class, BaseApiController::class);
    }
}
