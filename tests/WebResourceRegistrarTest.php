<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Illuminate\Contracts\Routing\Registrar;
use Mockery\MockInterface;
use Saritasa\Laravel\Controllers\BaseController;
use Saritasa\Laravel\Controllers\Web\WebResourceRegistrar;

class WebResourceRegistrarTest extends TestCase
{
    /** @var MockInterface */
    protected $routerMock;

    public function setUp()
    {
        $this->routerMock = \Mockery::mock(Registrar::class);
    }

    /**
     * Test resource method with default parameters.
     */
    public function testCreateDefaultResource()
    {
        $resourceName = str_random();
        $controllerName = 'controller';

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
                    $this->assertEquals("$resourceName", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.indexData",
                        'uses' => "$controllerName@indexData",
                        'mapping' => [],
                        'prefix' => 'ajax',
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/create", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.create",
                        'uses' => "$controllerName@create",
                        'mapping' => [],
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.show",
                        'uses' => "$controllerName@show",
                        'mapping' => []
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.read",
                        'uses' => "$controllerName@read",
                        'mapping' => [],
                        'prefix' => 'ajax',
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}/edit", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.edit",
                        'uses' => "$controllerName@edit",
                        'mapping' => [],
                    ], $options);
                }
            );
        $this->routerMock->shouldReceive('post')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.store",
                        'uses' => "$controllerName@store",
                        'mapping' => [],
                        'prefix' => 'ajax',
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
                        'mapping' => [],
                        'prefix' => 'ajax',
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
                        'mapping' => [],
                        'prefix' => 'ajax',
                    ], $options);
                }
            );

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName);
    }

    /**
     * Test resource method with custom options.
     */
    public function testCreateResourceWithOptions()
    {
        $resourceName = str_random();
        $controllerName = 'controller';
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

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test resource method with model binding.
     */
    public function testCreateResourceWithModelBindingDefaultName()
    {
        $resourceName = str_random();
        $controllerName = 'controller';
        $options = [
            'only' => ['show', 'update', 'destroy'],
            'get' => [],
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
                        'mapping' => [$shortName => $className],
                        'prefix' => 'ajax',
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
                        'mapping' => [$shortName => $className],
                        'prefix' => 'ajax',
                    ],
                        $options);
                }
            );

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, $className);
    }

    public function testExceptionWillThrownWithBadOptions()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'get' => false,
        ];
        $this->expectException(\InvalidArgumentException::class);
        $registrar = new WebResourceRegistrar($this->routerMock);
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

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->get($expectedPath, $controllerName, $action, $routeName, $mapping);
    }

    public function testActionsWithEmptyAction()
    {
        $expectedPath = str_random();
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, null, $routeName, $mapping);
        }
    }

    public function testActionWithEmptyAction()
    {
        $expectedPath = str_random();
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, null, $routeName, $mapping);
        }
    }

    public function testActionWithEmptyRoute()
    {
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    public function testActionWithEmptyRouteAndAction()
    {
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
            $registrar->$verb($expectedPath, $controllerName, $action, null, $mapping);
        }
    }

    protected function getVerbs()
    {
        return ['post', 'get', 'put', 'patch', 'delete'];
    }
}
