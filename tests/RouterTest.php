<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Saritasa\Exceptions\ModelNotFoundException;
use Illuminate\Routing\Route;
use Mockery\MockInterface;
use Saritasa\Contracts\IRepository;
use Saritasa\Contracts\IRepositoryFactory;
use Saritasa\Laravel\Controllers\Router;

class RouterTest extends TestCase
{
    /** @var MockInterface */
    protected $router;
    /** @var MockInterface */
    protected $route;
    /** @var MockInterface */
    protected $containerMock;
    /** @var MockInterface */
    protected $repositoryFactoryMock;

    public function setUp()
    {
        parent::setUp();
        $this->containerMock = \Mockery::mock(Container::class);
        $dispatcherMock = \Mockery::mock(Dispatcher::class);
        $this->repositoryFactoryMock = \Mockery::mock(IRepositoryFactory::class);
        $this->router = \Mockery::mock(Router::class, [$this->repositoryFactoryMock, $dispatcherMock, $this->containerMock])
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

        $expectedModel = new TempModel(['id' => $nameParameter]);

        $repositoryMock = \Mockery::mock(IRepository::class);

        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([TempModel::class])
            ->andReturn($repositoryMock);

        $repositoryMock->shouldReceive('findOrFail')->withArgs([$nameParameter])->andReturnUsing(function(string $value) {
            return new TempModel(['id' => $value]);
        });

        $this->route->shouldReceive('setParameter')->andReturnUsing(function (string $name, $actualModel) use (
            $expectedModel
        ) {
            $this->assertEquals('name', $name);
            $this->assertEquals($expectedModel, $actualModel);
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

        $repositoryMock = \Mockery::mock(IRepository::class);
        $repositoryMock->shouldReceive('getModelClass')->withArgs([])->andReturn($className);
        $repositoryMock->shouldReceive('findOrFail')
            ->withArgs([$nameParameter])
            ->andThrow(new ModelNotFoundException($repositoryMock, $nameParameter));
        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([$className])
            ->andReturn($repositoryMock);

        $this->expectException(ModelNotFoundException::class);
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
        $expectedModel = new TempModel(['id' => $nameParameter]);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);
        $repositoryMock = \Mockery::mock(IRepository::class);

        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([$className])
            ->andReturn($repositoryMock);

        $repositoryMock->shouldReceive('findOrFail')->withArgs([$nameParameter])->andReturnUsing(function(string $value) {
            return new TempModel(['id' => $value]);
        });

        $this->route->shouldReceive('setParameter')->andReturnUsing(function (string $name, $actualModel) use (
            $expectedModel
        ) {
            $this->assertEquals('name', $name);
            $this->assertEquals($expectedModel, $actualModel);
        });

        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    protected function getRoutableModel(): MockInterface
    {
        return \Mockery::mock(UrlRoutable::class);
    }
}

class TempModel extends Model
{
    protected $fillable = ['id'];
}
