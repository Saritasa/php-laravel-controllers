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
}
