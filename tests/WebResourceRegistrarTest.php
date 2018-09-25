<?php

namespace Saritasa\LaravelControllers\Tests;

use Illuminate\Contracts\Routing\Registrar;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;
use Saritasa\LaravelControllers\BaseController;
use Saritasa\LaravelControllers\Web\WebResourceRegistrar;

/**
 * Tests for web resource registrar.
 */
class WebResourceRegistrarTest extends TestCase
{
    /**
     * Router mock.
     *
     * @var MockInterface|Registrar
     */
    protected $routerMock;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->routerMock = Mockery::mock(Registrar::class);
    }

    /**
     * Test resource method with default parameters.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testCreateDefaultResource(): void
    {
        $resourceName = str_random();
        $controllerName = 'controller';

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.index",
                        'uses' => "$controllerName@index",
                        'mapping' => [],
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.indexData",
                        'uses' => "$controllerName@indexData",
                        'mapping' => [],
                        'prefix' => 'ajax',
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/create", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.create",
                        'uses' => "$controllerName@create",
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
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals([
                        'as' => "$resourceName.read",
                        'uses' => "$controllerName@read",
                        'mapping' => [],
                        'prefix' => 'ajax',
                    ], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
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
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
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
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
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
                function (string $resource, array $options) use ($resourceName, $controllerName): void {
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
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testCreateResourceWithOptions(): void
    {
        $resourceName = str_random();
        $controllerName = 'controller';
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

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test resource method with model binding.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testCreateResourceWithModelBindingDefaultName(): void
    {
        $resourceName = str_random();
        $controllerName = 'controller';
        $options = [
            'only' => ['show', 'update', 'destroy'],
            'get' => [],
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
                        'prefix' => 'ajax',
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
                        'prefix' => 'ajax',
                        ],
                        $options
                    );
                }
            );

        $registrar = new WebResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, $className);
    }

    /**
     * Test that exception will be thrown if not valid options passed.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testExceptionWillThrownWithBadOptions(): void
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'get' => false,
        ];
        $this->expectException(InvalidArgumentException::class);
        $registrar = new WebResourceRegistrar($this->routerMock);
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
        $controllerName = 'controller';
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

        $registrar = new WebResourceRegistrar($this->routerMock);
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
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
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
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
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
        $controllerName = 'controller';
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

            $registrar = new WebResourceRegistrar($this->routerMock);
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
