<?php

namespace Saritasa\Laravel\Controllers;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use ReflectionParameter;

/**
 * Custom application router.
 */
class Router extends LaravelRouter
{
    /**
     * Substitute the implicit Eloquent model bindings for the route.
     *
     * @param Route $route Current route
     *
     * @return void
     */
    public function substituteImplicitBindings($route)
    {
        $mapping = $route->getAction('mapping') ?? [];
        $parameters = $route->parameters();

        /**
         * Route parameter.
         *
         * @var ReflectionParameter $parameter
         */
        foreach ($route->signatureParameters(UrlRoutable::class) as $parameter) {
            if (!$parameterName = $this->getParameterName($parameter->name, $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }

            $modelClass = $mapping[$parameterName] ?? $parameter->getClass()->name;

            $instance = $this->container->make($modelClass);

            if (!$model = $instance->resolveRouteBinding($parameterValue)) {
                throw (new ModelNotFoundException())->setModel(get_class($instance));
            }

            $route->setParameter($parameterName, $model);
        }
    }

    /**
     * Return the parameter name if it exists in the given parameters.
     *
     * @param string $name Router parameter to check
     * @param array $parameters Expected parameters
     * @return string|null
     */
    protected function getParameterName(string $name, array $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }

        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }

        return null;
    }
}
