<?php

namespace Saritasa\LaravelControllers\Tests;

use Dingo\Api\Routing\Router;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;
use Saritasa\LaravelControllers\Api\ApiResourceRegistrar;
use Saritasa\LaravelControllers\BaseController;

/**
 * Tests for api resource registrar.
 */
class ApiResourceRegistrarTest extends TestCase
{
    /**
     * Router instance mock.
     *
     * @var MockInterface|Router
     */
    protected $routerMock;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->routerMock = Mockery::mock(Router::class);
    }

    /**
     * Test creation of default resource.
     *
     * @throws ReflectionException
     *
     * @return void
     */
    public function testCreateDefaultResource(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/count", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.count",
                        'uses' => "$controllerName@count",
                        'mapping' => [],
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.index",
                        'uses' => "$controllerName@index",
                        'mapping' => [],
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('post')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.store",
                        'uses' => "$controllerName@store",
                        'mapping' => [],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => [],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => [],
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName);
    }

    /**
     * Test creation of resource with passing additional options.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testCreateResourceWithOptions(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'only' => 'show',
        ];

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [],
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test creation of resource with default model binding.
     *
     * @throws ReflectionException
     *
     * @return void
     */
    public function testCreateResourceWithModelBindingDefaultName(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            ApiResourceRegistrar::OPTION_ONLY => ['show', 'update', 'destroy',],
        ];
        $className = BaseController::class;
        $shortName = lcfirst((new ReflectionClass($className))->getShortName());

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [$shortName => $className],
                    ], $options);
                }
            );

        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => [$shortName => $className],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals(
                        [
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => [$shortName => $className],
                        ],
                        $options
                    );
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, $className);
    }

    /**
     * Test creation of resource with custom model binding.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testCreateResourceWithModelBindingWithCustomName(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            ApiResourceRegistrar::OPTION_EXCEPT => ['index', 'store', 'count'],
        ];
        $className = BaseController::class;
        $customName = lcfirst(str_random());

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [$customName => $className],
                    ], $options);
                }
            );

        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => [$customName => $className],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (
                    string $resource,
                    array $options
                ) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ): void {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => [$customName => $className],
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, $className, $customName);
    }

    /**
     * Test that exception will be thrown if not valid options passed.
     *
     * @throws ReflectionException
     *
     * @return void
     */
    public function testExceptionWillThrownWithBadOptions(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'get' => false,
        ];
        $this->expectException(InvalidArgumentException::class);
        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test action method with passing all available params.
     *
     * @return void
     */
    public function testActionMethodWithAllParams(): void
    {
        $expectedPath = str_random();
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $action = str_random();
        $routeName = str_random();

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (
                    string $path,
                    array $options
                ) use (
                    $expectedPath,
                    $controllerName,
                    $mapping,
                    $action,
                    $routeName
                ): void {
                    $this->assertEquals($expectedPath, $path);
                    $this->assertEquals([
                        'as' => $routeName,
                        'uses' => "$controllerName@$action",
                        'mapping' => $mapping,
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->get($expectedPath, $controllerName, $action, $routeName, $mapping);
    }

    /**
     * Test action method with empty action param.
     *
     * @return void
     */
    public function testActionsWithEmptyAction(): void
    {
        $expectedPath = str_random();
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $routeName = str_random();

        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (
                        string $path,
                        array $options
                    ) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $routeName
                    ): void {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $routeName,
                            'uses' => "$controllerName@$expectedPath",
                            'mapping' => $mapping,
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, null, $routeName, $mapping);
        }
    }

    /**
     * Test action method with empty route param.
     *
     * @return void
     */
    public function testActionWithEmptyRoute(): void
    {
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $action = str_random();
        $expectedPath = str_random();
        $route = strtolower($expectedPath . '.' . $action);
        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (
                        string $path,
                        array $options
                    ) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $action,
                        $route
                    ): void {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $route,
                            'uses' => "$controllerName@$action",
                            'mapping' => $mapping,
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    /**
     * Test action method with empty action and route param.
     *
     * @return void
     */
    public function testActionWithEmptyRouteAndAction(): void
    {
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $expectedPath = str_random();
        $action = $expectedPath;
        $route = strtolower($action);
        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (
                        string $path,
                        array $options
                    ) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $action,
                        $route
                    ): void {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $route,
                            'uses' => "$controllerName@$action",
                            'mapping' => $mapping,
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    /**
     * Return available route verbs.
     *
     * @return array
     */
    protected function getVerbs(): array
    {
        return ['post', 'get', 'put', 'patch', 'delete'];
    }
}
