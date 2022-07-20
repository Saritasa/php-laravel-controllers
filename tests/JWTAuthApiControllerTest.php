<?php

namespace Saritasa\LaravelControllers\Tests;

use Dingo\Api\Http\Response;
use Dingo\Api\Http\Response\Factory as ResponseFactory;
use Illuminate\Support\Str;
use Mockery;
use Mockery\MockInterface;
use Saritasa\LaravelControllers\Api\JWTAuthApiController;
use Saritasa\LaravelControllers\Requests\LoginRequest;
use Saritasa\LaravelControllers\Responses\AuthSuccess;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Jwt api controller test.
 */
class JWTAuthApiControllerTest extends TestCase
{
    /**
     * JWT auth mock.
     *
     * @var MockInterface|JWTAuth
     */
    protected $jwtAuthMock;

    /**
     * JWT auth mock.
     *
     * @var MockInterface|JWTAuthApiController
     */
    protected $jwtApiController;

    /**
     * Prepare tests for run.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->jwtAuthMock = Mockery::mock(JWTAuth::class);
        $this->jwtApiController = Mockery::mock(JWTAuthApiController::class, [$this->jwtAuthMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    /**
     * Test success login.
     *
     * @return void
     */
    public function testLoginSuccess(): void
    {
        $credentials = [
            'email' => Str::random(),
            'password' => Str::random(),
        ];
        $loginRequestMock = $this->makeLoginRequestMock($credentials);

        $token = Str::random();
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

        $this->jwtApiController
            ->shouldReceive('json')
            ->andReturnUsing(function (AuthSuccess $actualSuccess) use (
                $authSuccess,
                $expectedResult
            ) {
                $this->assertEquals($actualSuccess, $authSuccess);
                return $expectedResult;
            });

        $actualResult = $this->jwtApiController->login($loginRequestMock);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test when login error.
     *
     * @return void
     */
    public function testLoginError(): void
    {
        $message = Str::random();
        Mocks::mockTranslator($message);
        $credentials = [
            'email' => Str::random(),
            'password' => Str::random(),
        ];
        $loginRequestMock = $this->makeLoginRequestMock($credentials);
        $responseFactoryMock = Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorNotFound')->withArgs([$message])->andThrow($exception);

        $this->jwtAuthMock->shouldReceive('attempt')->withArgs([$credentials])->andReturnFalse();

        $this->jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $this->jwtApiController->login($loginRequestMock);
    }

    /**
     * Test convert of JWT exception in http when create token error.
     *
     * @return void
     */
    public function testLoginConvertsJwtExceptionInHttpException(): void
    {
        $message = Str::random();
        Mocks::mockTranslator($message);
        $credentials = [
            'email' => Str::random(),
            'password' => Str::random(),
        ];

        $loginRequestMock = $this->makeLoginRequestMock($credentials);

        $responseFactoryMock = Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorInternal')->withArgs([$message])->andThrow($exception);

        $this->jwtAuthMock->shouldReceive('attempt')->withArgs([$credentials])->andThrow(new JWTException());

        $this->jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $this->jwtApiController->login($loginRequestMock);
    }

    /**
     * Test logout action.
     *
     * @return void
     */
    public function testLogout(): void
    {
        $expectedResult = new Response(null, 204);
        $responseFactoryMock = Mockery::mock(ResponseFactory::class);

        $responseFactoryMock->shouldReceive('noContent')->withArgs([])->andReturn($expectedResult);
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('invalidate')->withArgs([])->andReturnNull();

        $this->jwtApiController
            ->shouldReceive('response')
            ->andSet('response', $responseFactoryMock)
            ->andReturn($responseFactoryMock);

        $actualResult = $this->jwtApiController->logout();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test refresh token success.
     *
     * @return void
     */
    public function testRefreshTokenSuccess(): void
    {
        $newToken = Str::random();
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('refresh')->withArgs([])->andReturn($newToken);

        $authSuccess = new AuthSuccess($newToken);
        $expectedResult = new Response(json_encode(['token' => $newToken]));

        $this->jwtApiController
            ->shouldReceive('json')
            ->andReturnUsing(function (AuthSuccess $actualSuccess) use (
                $authSuccess,
                $expectedResult
            ) {
                $this->assertEquals($actualSuccess, $authSuccess);
                return $expectedResult;
            });

        $actualResult = $this->jwtApiController->refreshToken();
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * Test convert of JWT exception in http when refresh token error.
     *
     * @return void
     * @dis
     */
    public function testRefreshTokenConvertsJwtExceptionInHttpException(): void
    {
        $message = Str::random();
        Mocks::mockTranslator($message);

        $responseFactoryMock = Mockery::mock(ResponseFactory::class);
        $exception = new HttpException(100, $message);

        $responseFactoryMock->shouldReceive('errorForbidden')->withArgs([$message])->andThrow($exception);
        $this->jwtAuthMock->shouldReceive('parseToken')->withArgs([])->andReturnSelf();
        $this->jwtAuthMock->shouldReceive('refresh')->withArgs([])->andThrow(new JWTException());

        $this->jwtApiController->shouldReceive('response')->andSet(
            'response',
            $responseFactoryMock
        )->andReturn($responseFactoryMock);

        $this->expectExceptionObject($exception);

        $this->jwtApiController->refreshToken();
    }

    /**
     * Make mock of login request with given credentials.
     *
     * @param array $credentials Credentials to make login request mock
     *
     * @return MockInterface|LoginRequest
     */
    protected function makeLoginRequestMock(array $credentials): MockInterface
    {
        $loginRequestMock = Mockery::mock(LoginRequest::class);
        $loginRequestMock->shouldReceive('only')->withArgs(['email', 'password'])->andReturn($credentials);
        return $loginRequestMock;
    }
}
