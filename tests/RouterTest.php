<?php

namespace Saritasa\LaravelControllers\Tests;

use Illuminate\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Routing\Route;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use Saritasa\LaravelControllers\Router;
use Saritasa\LaravelRepositories\Contracts\IRepository;
use Saritasa\LaravelRepositories\Contracts\IRepositoryFactory;
use Saritasa\LaravelRepositories\Exceptions\ModelNotFoundException;
use Saritasa\LaravelRepositories\Exceptions\RepositoryException;

/**
 * Tests for router.
 */
class RouterTest extends TestCase
{
    /**
     * Router mock.
     *
     * @var MockInterface|Router
     */
    protected $router;

    /**
     * Route mock.
     *
     * @var MockInterface|Route
     */
    protected $route;

    /**
     * DI container mock.
     *
     * @var MockInterface|Container
     */
    protected $containerMock;

    /**
     * Repository factory mock.
     *
     * @var MockInterface|IRepositoryFactory
     */
    protected $repositoryFactoryMock;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->containerMock = Mockery::mock(Container::class);
        $dispatcherMock = Mockery::mock(Dispatcher::class);
        $this->repositoryFactoryMock = Mockery::mock(IRepositoryFactory::class);
        $this->router = Mockery::mock(
            Router::class,
            [$this->repositoryFactoryMock, $dispatcherMock, $this->containerMock]
        )
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $this->route = Mockery::mock(Route::class);
    }

    /**
     * Test that if model not implement Routable contract it will no set to params.
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function testIfModelNotRoutableItWillNotSet(): void
    {
        $firstParameter = Mocks::mockReflectionParameter('name');

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([]);
        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $this->mockRoutable(),
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);
        $result = $this->router->substituteImplicitBindings($this->route);
        $this->assertNull($result);
    }

    /**
     * Test when mapping from route has needed parameter.
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function testIfMappingHasParameter(): void
    {
        $firstParameter = Mocks::mockReflectionParameter('name');

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([
            'name' => TestModel::class,
        ]);

        $nameParameter = rand(0, 100);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);

        $expectedModel = new TestModel(['id' => $nameParameter]);

        $repositoryMock = Mocks::mockRepository(str_random());

        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([TestModel::class])
            ->andReturn($repositoryMock);

        $repositoryMock->shouldReceive('findWhere')
            ->andReturnUsing(function (array $filters) {
                $testModel = new TestModel();
                $this->assertEquals(1, count($filters));
                $this->assertEquals($testModel->getRouteKeyName(), key($filters));

                $testModel->setAttribute($testModel->getRouteKeyName(), $filters[$testModel->getRouteKeyName()]);

                return $testModel;
            });

        $this->route->shouldReceive('setParameter')
            ->andReturnUsing(function (
                string $name,
                $actualModel
            ) use (
                $expectedModel
            ): void {
                $this->assertEquals('name', $name);
                $this->assertEquals($expectedModel, $actualModel);
            });

        $this->router->substituteImplicitBindings($this->route);
    }

    /**
     * Test that exception will be thrown if model can be found with given route parameters.
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function testExceptionWillThrownIfModelNotFound(): void
    {
        $firstParameter = Mocks::mockReflectionParameter('Name');
        $className = TestModel::class;
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

        $repositoryMock = Mocks::mockRepository($className);
        $repositoryMock->shouldReceive('findWhere')->andReturnNull();
        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([$className])
            ->andReturn($repositoryMock);

        $this->expectException(ModelNotFoundException::class);
        $this->router->substituteImplicitBindings($this->route);
    }

    /**
     * Test when mapping has not any parameters.
     *
     * @return void
     *
     * @throws ModelNotFoundException
     * @throws RepositoryException
     */
    public function testIfMappingHasNotParameters(): void
    {
        $firstParameter = Mocks::mockReflectionParameter('name')
        ->shouldAllowMockingProtectedMethods();
        $className = TestModel::class;
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionClass->shouldReceive('getName')->withArgs([])->andReturn($className);
        $firstParameter->shouldReceive('getClass')
            ->withArgs([])
            ->andReturn($reflectionClass);
        $firstParameter->shouldReceive('name')->andReturn($className);

        $this->route->shouldReceive('getAction')->withArgs(['mapping'])->andReturn([]);

        $nameParameter = rand(0, 100);
        $expectedModel = new TestModel(['id' => $nameParameter]);

        $this->route->shouldReceive('parameters')->withArgs([])->andReturn([
            'name' => $nameParameter,
        ]);
        $this->route->shouldReceive('signatureParameters')
            ->withArgs([UrlRoutable::class])
            ->andReturn([$firstParameter]);

        $repositoryMock = Mockery::mock(IRepository::class);

        $this->repositoryFactoryMock
            ->shouldReceive('getRepository')
            ->withArgs([$className])
            ->andReturn($repositoryMock);

        $repositoryMock->shouldReceive('findWhere')
            ->andReturnUsing(function (array $filters) {
                $testModel = new TestModel();
                $this->assertEquals(1, count($filters));
                $this->assertEquals($testModel->getRouteKeyName(), key($filters));

                $testModel->setAttribute($testModel->getRouteKeyName(), $filters[$testModel->getRouteKeyName()]);

                return $testModel;
            });

        $this->route->shouldReceive('setParameter')
            ->andReturnUsing(function (
                string $name,
                $actualModel
            ) use (
                $expectedModel
            ): void {
                $this->assertEquals('name', $name);
                $this->assertEquals($expectedModel, $actualModel);
            });

        $this->router->substituteImplicitBindings($this->route);
    }

    /**
     * Create mock of routable model.
     *
     * @return MockInterface|UrlRoutable
     */
    protected function mockRoutable(): MockInterface
    {
        return Mockery::mock(UrlRoutable::class);
    }
}
