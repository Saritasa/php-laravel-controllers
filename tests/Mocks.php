<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Illuminate\Contracts\Translation\Translator;
use Mockery\MockInterface;

class Mocks
{
    public static function mockTranslator(string $message): MockInterface
    {
        $translator = \Mockery::mock(Translator::class);
        $translator->shouldReceive('trans')->andReturn($message);
        app()->instance('translator', $translator);
        return $translator;
    }

    public static function mockReflectionParameter(string $parameterName): MockInterface
    {
        $parameter = \Mockery::mock(\ReflectionParameter::class);
        $parameter->shouldReceive('getName')->andReturn($parameterName);
        return $parameter;
    }
}
