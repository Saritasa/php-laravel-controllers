<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Dingo\Api\Routing\Router;
use Mockery\MockInterface;
use Saritasa\Laravel\Controllers\Api\ApiResourceRegistrar;
use Saritasa\Laravel\Controllers\BaseController;

/**
 * Api resource registrar test
 */
class ApiResourceRegistrarTest extends TestCase
{
    /** @var MockInterface */
    protected $routerMock;

    public function setUp()
    {
        $this->routerMock = \Mockery::mock(Router::class);
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

        $registrar = new ApiResourceRegistrar($this->routerMock);
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

        $registrar = new ApiResourceRegistrar($this->routerMock);
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
            'only' => ['show' => true, 'update' => true, 'destroy' => true],
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

        $registrar = new ApiResourceRegistrar($this->routerMock);
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
            'only' => ['show' => true, 'update' => true, 'destroy' => true],
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

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, $className, $customName);
    }
}
