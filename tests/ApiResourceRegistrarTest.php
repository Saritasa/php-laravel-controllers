<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Dingo\Api\Routing\Router;
use Illuminate\Contracts\Foundation\Application;
use Mockery\MockInterface;
use InvalidArgumentException;
use Saritasa\Laravel\Controllers\Api\ApiResourceRegistrar;
use Saritasa\Laravel\Controllers\BaseController;
use Saritasa\Laravel\Controllers\Contracts\IResourceController;

/**
 * Api resource registrar test
 */
class ApiResourceRegistrarTest extends TestCase
{
    /** @var MockInterface */
    protected $routerMock;

        /** @var MockInterface */
    protected $applicationMock;

    public function setUp()
    {
        $this->routerMock = \Mockery::mock(Router::class);
        $this->applicationMock = \Mockery::mock(Application::class);
    }

    /**
     * Test resource method with default parameters.
     */
    public function testCreateDefaultResource()
    {
        $resourceName = str_random();
        $controllerName = str_random();

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.index",
                        'uses' => "$controllerName@index",
                        'mapping' => []
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => []
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('post')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.create",
                        'uses' => "$controllerName@create",
                        'mapping' => []
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => []
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => []
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->resource($resourceName, $controllerName);
    }

    /**
     * Test resource method with custom options.
     */
    public function testCreateResourceWithOptions()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'only' => 'show',
        ];

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => []
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test resource method with model binding.
     */
    public function testCreateResourceWithModelBindingDefaultName()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            ApiResourceRegistrar::OPTION_ONLY => ['show', 'update', 'destroy',],
        ];
        $className = BaseController::class;
        $shortName = lcfirst((new \ReflectionClass($className))->getShortName());

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [$shortName => $className]
                    ], $options);
                }
            );

        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => [$shortName => $className]
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $shortName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => [$shortName => $className]
                    ],
                        $options);
                }
            );

        $controllerMock = \Mockery::mock(IResourceController::class);
        $controllerMock->shouldReceive('setModelClass')->withArgs([$className])->andReturnNull();
        $this->applicationMock->shouldReceive('make')->withArgs([$controllerName])->andReturn($controllerMock);
        $this->applicationMock->shouldReceive('instance')->withArgs([$controllerName, $controllerMock])->andReturnNull();
        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->resource($resourceName, $controllerName, $options, $className);
    }

    /**
     * Test resource method with model binding.
     */
    public function testCreateResourceWithModelBindingWithCustomName()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
           ApiResourceRegistrar::OPTION_EXPECT => ['index', 'create',],
        ];
        $className = BaseController::class;
        $customName = lcfirst(str_random());

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => [$customName => $className]
                    ], $options);
                }
            );

        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.update",
                        'uses' => "$controllerName@update",
                        'mapping' => [$customName => $className]
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use (
                    $resourceName,
                    $controllerName,
                    $customName,
                    $className
                ) {
                    $this->assertEquals("$resourceName/{{$customName}}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.destroy",
                        'uses' => "$controllerName@destroy",
                        'mapping' => [$customName => $className]
                    ], $options);
                }
            );

        $controllerMock = \Mockery::mock(IResourceController::class);
        $controllerMock->shouldReceive('setModelClass')->withArgs([$className])->andReturnNull();
        $this->applicationMock->shouldReceive('make')->withArgs([$controllerName])->andReturn($controllerMock);
        $this->applicationMock->shouldReceive('instance')->withArgs([$controllerName, $controllerMock])->andReturnNull();

        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->resource($resourceName, $controllerName, $options, $className, $customName);
    }

    public function testExceptionWillThrownWithBadOptions()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'get' => false,
        ];
        $this->expectException(InvalidArgumentException::class);
        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    public function testActionMethodWithAllParams()
    {
        $expectedPath = str_random();
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $action = str_random();
        $routeName = str_random();

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $path, array $options) use (
                    $expectedPath,
                    $controllerName,
                    $mapping,
                    $action,
                    $routeName
                ) {
                    $this->assertEquals($expectedPath, $path);
                    $this->assertEquals([
                        'as' => $routeName,
                        'uses' => "$controllerName@$action",
                        'mapping' => $mapping
                    ], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
        $registrar->get($expectedPath, $controllerName, $action, $routeName, $mapping);
    }

    public function testActionsWithEmptyAction()
    {
        $expectedPath = str_random();
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $routeName = str_random();

        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (string $path, array $options) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $routeName
                    ) {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $routeName,
                            'uses' => "$controllerName@$expectedPath",
                            'mapping' => $mapping
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
            $registrar->$verb($expectedPath, $controllerName, null, $routeName, $mapping);
        }
    }

    public function testActionWithEmptyAction()
    {
        $expectedPath = str_random();
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $routeName = str_random();
        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (string $path, array $options) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $routeName
                    ) {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $routeName,
                            'uses' => "$controllerName@$expectedPath",
                            'mapping' => $mapping
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
            $registrar->$verb($expectedPath, $controllerName, null, $routeName, $mapping);
        }
    }

    public function testActionWithEmptyRoute()
    {
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $action = str_random();
        $expectedPath = str_random();
        $route = strtolower($expectedPath . '.' . $action);
        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (string $path, array $options) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $action,
                        $route
                    ) {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $route,
                            'uses' => "$controllerName@$action",
                            'mapping' => $mapping
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    public function testActionWithEmptyRouteAndAction()
    {
        $controllerName = str_random();
        $mapping = [str_random() => str_random()];
        $expectedPath = str_random();
        $action = $expectedPath;
        $route = strtolower($action);
        foreach ($this->getVerbs() as $verb) {
            $this->routerMock->shouldReceive($verb)
                ->andReturnUsing(
                    function (string $path, array $options) use (
                        $expectedPath,
                        $controllerName,
                        $mapping,
                        $action,
                        $route
                    ) {
                        $this->assertEquals($expectedPath, $path);
                        $this->assertEquals([
                            'as' => $route,
                            'uses' => "$controllerName@$action",
                            'mapping' => $mapping
                        ], $options);
                    }
                );

            $registrar = new ApiResourceRegistrar($this->routerMock, $this->applicationMock, $this->applicationMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    protected function getVerbs()
    {
        return ['post', 'get', 'put', 'patch', 'delete'];
    }
}
