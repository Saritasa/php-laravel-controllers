<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Route;
use Mockery\MockInterface;
use Saritasa\Laravel\Controllers\Router;

class RouterTest extends TestCase
{
    /** @var MockInterface */
    protected $router;
    /** @var MockInterface */
    protected $route;
    /** @var MockInterface */
    protected $containerMock;

    public function setUp()
    {
        parent::setUp();
        $this->containerMock = \Mockery::mock(Container::class);
        $dispatcherMock = \Mockery::mock(Dispatcher::class);
        $this->router = \Mockery::mock(Router::class, [$dispatcherMock, $this->containerMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->route = \Mockery::mock(Route::class);
    }

    public function testNoOneParameterWasSetToRoute()
    {
        $firstParameter = Mocks::mockReflectionParameter('name');
        $secondParameter = Mocks::mockReflectionParameter('url');

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([]);
        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter, $secondParameter]);
        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    public function testIfModelNotRoutableItWillNotSet()
    {
        $firstParameter = Mocks::mockReflectionParameter('name');

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([]);
        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $this->getRoutableModel(),
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);
        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    public function testIfMappingHasParameter()
    {
        $firstParameter = Mocks::mockReflectionParameter('name');

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([
            'name' => TempModel::class,
        ]);

        $nameParameter = rand(0, 100);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);

        $tempModelMock = \Mockery::mock(TempModel::class);

        $this->containerMock->shouldReceive('make')->withArgs([TempModel::class])->andReturn($tempModelMock);
        $tempModelMock->shouldReceive('resolveRouteBinding')
            ->withArgs([$nameParameter])
            ->andSet('id', $nameParameter)
            ->andReturnSelf();

        $this->route->shouldReceive('setParameter')->andReturnUsing(function (string $name, $actualModel) use (
            $tempModelMock
        ) {
            $this->assertEquals('name', $name);
            $this->assertEquals($tempModelMock, $actualModel);
        });

        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    public function testExpcetionWillThrownIfModelNotFound()
    {
        $firstParameter = Mocks::mockReflectionParameter('Name');
        $className = str_random();
        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([
            'name' => $className,
        ]);

        $nameParameter = rand(0, 100);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);

        $tempModelMock = \Mockery::mock(TempModel::class);

        $this->containerMock->shouldReceive('make')->withArgs([$className])->andReturn($tempModelMock);
        $tempModelMock->shouldReceive('resolveRouteBinding')
            ->withArgs([$nameParameter])
            ->andReturnNull();
        $this->expectExceptionObject((new ModelNotFoundException())->setModel(get_class($tempModelMock)));
        $this->router->substituteImplicitBindings($this->route);
    }

    public function testIfMappingHasNotParameters()
    {
        $firstParameter = Mocks::mockReflectionParameter('name')
        ->shouldAllowMockingProtectedMethods();
        $className = str_random();
        $reflectionClass = \Mockery::mock(\ReflectionClass::class);
        $reflectionClass->shouldReceive('getName')->withArgs([])->andReturn($className);
        $firstParameter->shouldReceive('getClass')
            ->withArgs([])
            ->andReturn($reflectionClass);
        $firstParameter->shouldReceive('name')->andReturn($className);

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([]);

        $nameParameter = rand(0, 100);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);

        $tempModelMock = \Mockery::mock(TempModel::class);

        $this->containerMock->shouldReceive('make')->withArgs([$className])->andReturn($tempModelMock);
        $tempModelMock->shouldReceive('resolveRouteBinding')
            ->withArgs([$nameParameter])
            ->andSet('id', $nameParameter)
            ->andReturnSelf();

        $this->route->shouldReceive('setParameter')->andReturnUsing(function (string $name, $actualModel) use (
            $tempModelMock
        ) {
            $this->assertEquals('name', $name);
            $this->assertEquals($tempModelMock, $actualModel);
        });

        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    protected function getRoutableModel(): MockInterface
    {
        return \Mockery::mock(UrlRoutable::class);
    }
}

class TempModel implements UrlRoutable
{
    public function getRouteKey()
    {
    }

    public function getRouteKeyName()
    {
    }

    public function resolveRouteBinding($value)
    {
    }
}
