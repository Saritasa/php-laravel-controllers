<?php

namespace Saritasa\Laravel\Controllers\Tests;

use Dingo\Api\Http\Response;
use Dingo\Api\Http\Response\Factory as ResponseFactory;
use Illuminate\Contracts\Translation\Translator;
use Mockery\MockInterface;
use Saritasa\Laravel\Controllers\Api\JWTAuthApiController;
use Saritasa\Laravel\Controllers\Requests\LoginRequest;
use Saritasa\Laravel\Controllers\Responses\AuthSuccess;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Jwt api controller test.
 */
class JWTAuthApiControllerTest extends TestCase
{
    /** @var MockInterface */
    protected $jwtAuthMock;

    public function setUp()
    {
        parent::setUp();
        $this->jwtAuthMock = \Mockery::mock(JWTAuth::class);
    }

    public function testLoginSuccess()
    {
        $credentials = [
            'email' => str_random(),
            'password' => str_random(),
        ];
        $loginRequestMock = \Mockery::mock(LoginRequest::class);
        $loginRequestMock->shouldReceive('only')->withArgs(['email', 'password'])->andReturn($credentials);

        $token = str_random();
        $this->jwtAuthMock
            ->shouldReceive('attempt')
            ->andReturnUsing(function (array $passedCredentials) use (
                $credentials,
                $token
            ) {
                $this->assertEquals($passedCredentials, $credentials);
                return $token;
            });
        $authSuccess = new AuthSuccess($token);
        $expectedResult = new Response(json_encode(['token' => $token]));
        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController
            ->shouldReceive('json')
            ->andReturnUsing(function (AuthSuccess $actualSuccess) use (
                $authSuccess,
                $expectedResult
            ) {
                $this->assertEquals($actualSuccess, $authSuccess);
                return $expectedResult;
            });

        $actualResult = $jwtApiController->login($loginRequestMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testLoginError()
    {
        $message = str_random();
        $translator = \Mockery::mock(Translator::class);
        $translator->shouldReceive('trans')->andReturn($message);
        app()->instance('translator', $translator);
        $credentials = [
            'email' => str_random(),
            'password' => str_random(),
        ];
        $loginRequestMock = \Mockery::mock(LoginRequest::class);
        $loginRequestMock->shouldReceive('only')->withArgs(['email', 'password'])->andReturn($credentials);
        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorNotFound')->withArgs([$message])->andThrow($exception);

        $this->jwtAuthMock->shouldReceive('attempt')->withArgs([$credentials])->andReturnFalse();

        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $jwtApiController->login($loginRequestMock);
    }

    public function testLoginConvertsJwtExceptionInHttpException()
    {
        $message = str_random();
        $translator = \Mockery::mock(Translator::class);
        $translator->shouldReceive('trans')->andReturn($message);
        app()->instance('translator', $translator);
        $credentials = [
            'email' => str_random(),
            'password' => str_random(),
        ];

        $loginRequestMock = \Mockery::mock(LoginRequest::class);
        $loginRequestMock->shouldReceive('only')->withArgs(['email', 'password'])->andReturn($credentials);

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorInternal')->withArgs([$message])->andThrow($exception);

        $this->jwtAuthMock->shouldReceive('attempt')->withArgs([$credentials])->andThrow(new JWTException());

        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $jwtApiController->login($loginRequestMock);
    }

    public function testLogout()
    {
        $expectedResult = new Response(null, 204);
        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);

        $responseFactoryMock->shouldReceive('noContent')->withArgs([])->andReturn($expectedResult);
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('invalidate')->withArgs([])->andReturnNull();

        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResult = $jwtApiController->logout();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testRefreshTokenSuccess()
    {
        $newToken = str_random();
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('refresh')->withArgs([])->andReturn($newToken);

        $authSuccess = new AuthSuccess($newToken);
        $expectedResult = new Response(json_encode(['token' => $newToken]));
        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController
            ->shouldReceive('json')
            ->andReturnUsing(function (AuthSuccess $actualSuccess) use (
                $authSuccess,
                $expectedResult
            ) {
                $this->assertEquals($actualSuccess, $authSuccess);
                return $expectedResult;
            });

        $actualResult = $jwtApiController->refreshToken();
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testRefreshTokenConvertsJwtExceptionInHttpException()
    {
        $message = str_random();
        $translator = \Mockery::mock(Translator::class);
        $translator->shouldReceive('trans')->andReturn($message);
        app()->instance('translator', $translator);

        $responseFactoryMock = \Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorUnauthorized')->withArgs([$message])->andThrow($exception);
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('refresh')->withArgs([])->andThrow(new JWTException());

        $jwtApiController = \Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $jwtApiController->shouldReceive('response')->andSet('response',
            $responseFactoryMock)->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $jwtApiController->refreshToken();
    }

}
