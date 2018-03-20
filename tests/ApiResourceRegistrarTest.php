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
                    $this->assertEquals(['as' => "$resourceName.index", 'uses' => "$controllerName@index"], $options);
                },
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals(['as' => "$resourceName.show", 'uses' => "$controllerName@show"], $options);
                }
            );
        $this->routerMock->shouldReceive('post')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals($resourceName, $resource);
                    $this->assertEquals(['as' => "$resourceName.create", 'uses' => "$controllerName@create"], $options);
                }
            );
        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals(['as' => "$resourceName.update", 'uses' => "$controllerName@update"], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName) {
                    $this->assertEquals("$resourceName/{id}", $resource);
                    $this->assertEquals(['as' => "$resourceName.destroy", 'uses' => "$controllerName@destroy"], $options);
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
                    $this->assertEquals(['as' => "$resourceName.show", 'uses' => "$controllerName@show"], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options);
    }

    /**
     * Test resource method with model binding.
     */
    public function testCreateResourceWithModelBinding()
    {
        $resourceName = str_random();
        $controllerName = str_random();
        $options = [
            'only' => 'show, update, destroy',
        ];
        $className = BaseController::class;
        $shortName = (new \ReflectionClass($className))->getShortName();

        $this->routerMock->shouldReceive('get')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName, $shortName) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals(['as' => "$resourceName.show", 'uses' => "$controllerName@show"], $options);
                }
            );

        $this->routerMock->shouldReceive('put')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName, $shortName) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals(['as' => "$resourceName.update", 'uses' => "$controllerName@update"], $options);
                }
            );
        $this->routerMock->shouldReceive('delete')
            ->andReturnUsing(
                function (string $resource, array $options) use ($resourceName, $controllerName, $shortName) {
                    $this->assertEquals("$resourceName/{{$shortName}}", $resource);
                    $this->assertEquals(['as' => "$resourceName.destroy", 'uses' => "$controllerName@destroy"], $options);
                }
            );

        $registrar = new ApiResourceRegistrar($this->routerMock);
        $registrar->resource($resourceName, $controllerName, $options, lcfirst($className));
    }
}
