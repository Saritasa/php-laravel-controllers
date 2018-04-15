<?php

namespace Saritasa\Laravel\Controllers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlRoutable;
use Saritasa\Exceptions\ModelNotFoundException;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use ReflectionParameter;
use Saritasa\Contracts\IRepositoryFactory;
use Saritasa\Exceptions\RepositoryException;

/**
 * Custom application router which use route mapping to resolve model binding.
 */
class Router extends LaravelRouter
{
    protected $repositoryFactory;

    /**
     * Custom application router which use route mapping to resolve model binding.
     *
     * @param IRepositoryFactory $repositoryFactory
     * @param Dispatcher $dispatcher
     * @param Container $container
     */
    public function __construct(IRepositoryFactory $repositoryFactory, Dispatcher $dispatcher, Container $container)
    {
        parent::__construct($dispatcher, $container);
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Substitute the implicit Eloquent model bindings for the route.
     *
     * @param Route $route Current route
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
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
            if (!$parameterName = $this->getParameterName($parameter->getName(), $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }

            $modelClass = $mapping[$parameterName] ?? $parameter->getClass()->getName();
            $model = $this->repositoryFactory->getRepository($modelClass)->findOrFail($parameterValue);

            $route->setParameter($parameterName, $model);
        }
    }

    /**
     * Return the parameter name if it exists in the given parameters.
     *
     * @param string $name Router parameter to check
     * @param array $parameters Available parameters
     * @return string|null
     */
    protected function getParameterName(string $name, array $parameters): ?string
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
