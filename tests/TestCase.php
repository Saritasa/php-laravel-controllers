<?php

namespace Saritasa\LaravelControllers\Tests;

use Illuminate\Contracts\Console\Kernel;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Base package test case.
 */
class TestCase extends PhpUnitTestCase
{
    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return HttpKernelInterface
     */
    public function createApplication(): HttpKernelInterface
    {
        $app = require __DIR__.'/../vendor/autoload.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
