<?php

namespace Saritasa\LaravelControllers\Tests;

use Mockery;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use Saritasa\LaravelControllers\ControllerDispatcher;
use Mockery\MockInterface;

/**
 * Test that controller
 */
class ControllerDispatcherTest extends TestCase
{
    /**
     * Controller dispatcher mock.
     *
     * @var MockInterface|ControllerDispatcher
     */
    protected $dispatcher;

    /**
     * Reflection function mock.
     *
     * @var MockInterface|ReflectionFunctionAbstract
     */
    protected $reflectionFunction;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->reflectionFunction = Mockery::mock(ReflectionFunctionAbstract::class);
        $this->dispatcher = Mockery::mock(ControllerDispatcher::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * Tests that parameter will be returned if it resolved earlier.
     *
     * @return void
     */
    public function testParametersWillBeReturnedIfAlreadyResolved(): void
    {
        $parameters = ['id' => true];

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([
            Mocks::mockReflectionParameter('id')
        ]);

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals($parameters, $actualParameters);
    }

    /**
     * Tests that parameters will be returned if it not resolved yet but has not default value.
     *
     * @return void
     */
    public function testParametersWillBeReturnedIfNotResolvedYetButHasIsNullAndNotDefaultValue(): void
    {
        $parameters = ['id' => 1];

        $firstParameter = Mocks::mockReflectionParameter('name');
        $secondParameter = Mocks::mockReflectionParameter('url');
        $secondParameter->shouldReceive('isDefaultValueAvailable')->andReturnFalse();

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([$firstParameter, $secondParameter]);

        $this->dispatcher
            ->shouldReceive('transformDependency')
            ->andReturnUsing(function (
                ReflectionParameter $actualParameter,
                array $actualParameters
            ) use (
                $parameters,
                $firstParameter
            ) {
                $this->assertEquals($firstParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            }, function (
                ReflectionParameter $actualParameter,
                array $actualParameters
            ) use (
                $parameters,
                $secondParameter
            ) {
                $this->assertEquals($secondParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            });

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals($parameters, $actualParameters);
    }

    /**
     * Test that parameter will be returned with resolved model.
     *
     * @return void
     */
    public function testParametersWillBeReturnedWithResolvedInstances(): void
    {
        $parameters = ['id' => 1];

        $firstParameter = Mocks::mockReflectionParameter('name');
        $secondParameter = Mocks::mockReflectionParameter('url');
        $secondParameter->shouldReceive('isDefaultValueAvailable')->andReturnFalse();

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([$firstParameter, $secondParameter]);

        $tempModelClass = (new class()
        {
        });

        $this->dispatcher
            ->shouldReceive('transformDependency')
            ->andReturnUsing(function (
                ReflectionParameter $actualParameter,
                array $actualParameters
            ) use (
                $parameters,
                $firstParameter
            ) {
                $this->assertEquals($firstParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            }, function (
                ReflectionParameter $actualParameter,
                array $actualParameters
            ) use (
                $parameters,
                $secondParameter,
                $tempModelClass
            ) {
                $this->assertEquals($secondParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return $tempModelClass;
            });

        $this->dispatcher
            ->shouldReceive('spliceIntoParameters')
            ->withArgs([&$parameters, 'url', $tempModelClass])
            ->andReturnUsing(function (array &$parameters, string $key, $model) {
                array_splice(
                    $parameters,
                    $key,
                    0,
                    [$model]
                );
                return null;
            });

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals(array_merge($parameters, [$tempModelClass]), $actualParameters);
    }
}
