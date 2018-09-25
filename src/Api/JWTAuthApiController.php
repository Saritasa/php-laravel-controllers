<?php

namespace Saritasa\LaravelControllers\Api;

use Dingo\Api\Http\Response;
use Saritasa\LaravelControllers\Requests\LoginRequest;
use Saritasa\LaravelControllers\Responses\AuthSuccess;
use Saritasa\Transformers\IDataTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Authenticate API Controller. Uses JWT authentication.
 */
class JWTAuthApiController extends BaseApiController
{
    /**
     * Jwt auth service.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Authenticate API Controller. Uses JWT authentication.
     *
     * @param JWTAuth $jwtAuth Jwt guard
     * @param IDataTransformer $transformer Default transformer to apply to handled entity
     */
    public function __construct(JWTAuth $jwtAuth, ?IDataTransformer $transformer = null)
    {
        $this->jwtAuth = $jwtAuth;
        parent::__construct($transformer);
    }

    /**
     * Authenticate user by `email` and `password`.
     *
     * @param LoginRequest $request HTTP Request
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function login(LoginRequest $request): Response
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = $this->jwtAuth->attempt($credentials)) {
                $this->response->errorNotFound(trans('controllers::auth.failed'));
            }

            return $this->json(new AuthSuccess($token));
        } catch (JWTException $e) {
            $this->response->errorInternal(trans('controllers::auth.jwt_error'));
        }
    }

    /**
     * Logout user.
     * Invalidate access token.
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function logout(): Response
    {
        try {
            $this->jwtAuth->parseToken()->invalidate();
        } catch (JWTException $exception) {
            $this->response->errorUnauthorized(trans('controllers::auth.jwt_refresh_error'));
        }
        return $this->response->noContent();
    }

    /**
     * Refresh the access token.
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function refreshToken(): Response
    {
        try {
            return $this->json(new AuthSuccess($this->jwtAuth->parseToken()->refresh()));
        } catch (JWTException $e) {
            $this->response->errorUnauthorized(trans('controllers::auth.jwt_refresh_error'));
        }
    }
}
