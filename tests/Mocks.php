<?php

namespace Saritasa\LaravelControllers\Tests;

use Illuminate\Contracts\Translation\Translator;
use Mockery;
use Mockery\MockInterface;
use ReflectionParameter;
use Saritasa\LaravelRepositories\Contracts\IRepository;

/**
 * Helper to create common services mocks.
 */
class Mocks
{
    /**
     * Create translator mock.
     *
     * @param string $message Translator message
     *
     * @return MockInterface|Translator
     */
    public static function mockTranslator(string $message): MockInterface
    {
        $translator = Mockery::mock(Translator::class);
        $translator->allows([
            'trans' => $message,
            'get' => $message,
        ]);
        app()->instance('translator', $translator);
        return $translator;
    }

    /**
     * Create mock of reflection parameter.
     *
     * @param string $parameterName Parameter name to create mock
     *
     * @return MockInterface|ReflectionParameter
     */
    public static function mockReflectionParameter(string $parameterName): MockInterface
    {
        $parameter = Mockery::mock(ReflectionParameter::class);
        $parameter->shouldReceive('getName')->andReturn($parameterName);
        return $parameter;
    }

    /**
     * Create mock of repository.
     *
     * @param string $modelClass Model class which serves by repository
     *
     * @return MockInterface|IRepository
     */
    public static function mockRepository(string $modelClass): MockInterface
    {
        $repositoryMock = Mockery::mock(IRepository::class);
        $repositoryMock->shouldReceive('getModelClass')->withArgs([])->andReturn($modelClass);
        return $repositoryMock;
    }
}
