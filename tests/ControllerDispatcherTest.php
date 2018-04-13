<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Saritasa\Laravel\Controllers\ControllerDispatcher;
use Mockery\MockInterface;

class ControllerDispatcherTest extends TestCase
{
    /** @var MockInterface */
    protected $dispatcher;

    /** @var MockInterface */
    protected $reflectionFunction;

    public function setUp()
    {
        parent::setUp();
        $this->reflectionFunction = \Mockery::mock(\ReflectionFunction::class);
        $this->dispatcher = \Mockery::mock(ControllerDispatcher::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testParametersWillBeReturnedIfAlreadyResolved()
    {
        $parameters = ['id' => true];

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([
            Mocks::mockReflectionParameter('id')
        ]);

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals($parameters, $actualParameters);
    }

    public function testParametersWillBeReturnedIfNotResolvedYetButHasIsNullAndNotDefaultValue()
    {
        $parameters = ['id' => 1];

        $firstParameter = Mocks::mockReflectionParameter('name');
        $secondParameter = Mocks::mockReflectionParameter('url');
        $secondParameter->shouldReceive('isDefaultValueAvailable')->andReturnFalse();

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([$firstParameter, $secondParameter]);

        $this->dispatcher
            ->shouldReceive('transformDependency')
            ->andReturnUsing(function(\ReflectionParameter $actualParameter, array $actualParameters) use ($parameters, $firstParameter) {
                $this->assertEquals($firstParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            }, function(\ReflectionParameter $actualParameter, array $actualParameters) use ($parameters, $secondParameter) {
                $this->assertEquals($secondParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            });

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals($parameters, $actualParameters);
    }

    public function testParametersWillBeReturnedWithResolvedInstances()
    {
        $parameters = ['id' => 1];

        $firstParameter = Mocks::mockReflectionParameter('name');
        $secondParameter = Mocks::mockReflectionParameter('url');
        $secondParameter->shouldReceive('isDefaultValueAvailable')->andReturnFalse();

        $this->reflectionFunction->shouldReceive('getParameters')->andReturn([$firstParameter, $secondParameter]);

        $tempModelClass = (new class() {});

        $this->dispatcher
            ->shouldReceive('transformDependency')
            ->andReturnUsing(function(\ReflectionParameter $actualParameter, array $actualParameters) use ($parameters, $firstParameter) {
                $this->assertEquals($firstParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return null;
            }, function(\ReflectionParameter $actualParameter, array $actualParameters) use ($parameters, $secondParameter, $tempModelClass) {
                $this->assertEquals($secondParameter, $actualParameter);
                $this->assertEquals($parameters, $actualParameters);
                return $tempModelClass;
            });

        $this->dispatcher
            ->shouldReceive('spliceIntoParameters')
            ->withArgs([&$parameters, 'url', $tempModelClass])
            ->andReturnUsing(function(array &$parameters, string $key, $model) {
                array_splice(
                    $parameters, $key, 0, [$model]
                );
                return null;
            });

        $actualParameters = $this->dispatcher->resolveMethodDependencies($parameters, $this->reflectionFunction);

        $this->assertEquals(array_merge($parameters, [$tempModelClass]), $actualParameters);
    }
}
