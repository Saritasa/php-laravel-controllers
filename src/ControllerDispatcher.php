<?php

namespace Saritasa\Laravel\Controllers;

use Illuminate\Routing\ControllerDispatcher as LaravelControllerDispatcher;
use ReflectionFunctionAbstract;

/**
 * Resolver of controller dependencies.
 */
class ControllerDispatcher extends LaravelControllerDispatcher
{
    /**
     * Resolve the given method's type-hinted dependencies.
     *
     * @param  array $parameters Already resolved route parameters
     * @param  ReflectionFunctionAbstract $reflector Current controller method reflector
     * @return array
     */
    public function resolveMethodDependencies(array $parameters, ReflectionFunctionAbstract $reflector): array
    {
        $instanceCount = 0;

        $values = array_values($parameters);

        foreach ($reflector->getParameters() as $key => $parameter) {
            // If we resolved route parameter, we should not touch it
            if (isset($parameters[$parameter->getName()])) {
                continue;
            }

            $instance = $this->transformDependency(
                $parameter,
                $parameters
            );

            if (!is_null($instance)) {
                $instanceCount++;
                $this->spliceIntoParameters($parameters, $key, $instance);
            } elseif (!isset($values[$key - $instanceCount]) && $parameter->isDefaultValueAvailable()) {
                $this->spliceIntoParameters($parameters, $key, $parameter->getDefaultValue());
            }
        }

        return $parameters;
    }
}
