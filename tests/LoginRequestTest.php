<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Saritasa\Laravel\Controllers\Requests\LoginRequest;

class LoginRequestTest extends TestCase
{
    protected $loginRequest;

    public function setUp()
    {
        parent::setUp();
        $this->loginRequest = \Mockery::mock(LoginRequest::class)
            ->makePartial();
    }

    public function testAuthorize()
    {
        $this->assertTrue($this->loginRequest->authorize());
    }

    public function testRules()
    {
        $this->assertEquals([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], $this->loginRequest->rules());
    }
}
