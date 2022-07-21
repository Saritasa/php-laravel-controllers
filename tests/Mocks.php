<?php

namespace Saritasa\LaravelControllers\Tests;

use Illuminate\Contracts\Translation\Translator;
use Mockery;
use Mockery\MockInterface;
use ReflectionParameter;

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
}
