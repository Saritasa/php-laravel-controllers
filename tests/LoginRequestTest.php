<?php

namespace Saritasa\LaravelControllers\Tests;

use Mockery;
use Mockery\MockInterface;
use Saritasa\LaravelControllers\Requests\LoginRequest;

/**
 * Tests for login request.
 */
class LoginRequestTest extends TestCase
{
    /**
     * Login request mock.
     *
     * @var MockInterface|LoginRequest
     */
    protected $loginRequest;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->loginRequest = Mockery::mock(LoginRequest::class)->makePartial();
    }

    /**
     * Test authorize method.
     *
     * @return void
     */
    public function testAuthorize(): void
    {
        $this->assertTrue($this->loginRequest->authorize());
    }

    /**
     * Test that request has valid rules.
     *
     * @return void
     */
    public function testRules(): void
    {
        $this->assertEquals([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], $this->loginRequest->rules());
    }
}
