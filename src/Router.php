<?php

namespace Saritasa\LaravelControllers;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router as LaravelRouter;
use Illuminate\Support\Str;
use ReflectionParameter;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;
use Saritasa\LaravelRepositories\Exceptions\ModelNotFoundException;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

/**
 * Custom application router which use route mapping to resolve model binding.
 */
class Router extends LaravelRouter
{
    protected const ROUTE_MAPPING_KEY = 'mapping';

    /**
     * Factory for building specific repository for managed model.
     *
     * @var IRepositoryFactory
     */
    protected $repositoryFactory;

    /**
     * Custom application router which use route mapping to resolve model binding.
     *
     * @param IRepositoryFactory $repositoryFactory Repositories storage
     * @param Dispatcher $dispatcher Event dispatcher
     * @param Container $container Dependency injection container
     */
    public function __construct(IRepositoryFactory $repositoryFactory, Dispatcher $dispatcher, Container $container)
    {
        parent::__construct($dispatcher, $container);
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Substitute the implicit Eloquent model bindings for the route.
     *
     * @param Route $route Route to resolve model bindings
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function substituteImplicitBindings($route): void
    {
        $mapping = $route->getAction(static::ROUTE_MAPPING_KEY) ?? [];
        $parameters = $route->parameters();

        /**
         * Controller method parameter which candidate to resolve route parameters as model.
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
            /**
             * Empty url routable model to het route key name.
             *
             * @var UrlRoutable $emptyModel
             */
            $emptyModel = new $modelClass();
            $repository = $this->repositoryFactory->getRepository($modelClass);
            $model = $repository->findWhere([$emptyModel->getRouteKeyName() => $parameterValue]);

            if (!$model) {
                throw new ModelNotFoundException($repository, $parameterValue);
            }

            $route->setParameter($parameterName, $model);
        }
    }

    /**
     * Return the parameter name if it exists in the given parameters.
     *
     * @param string $name Router parameter to check
     * @param array $parameters Available parameters
     *
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
