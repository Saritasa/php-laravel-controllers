<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Response;
use Saritasa\Laravel\Controllers\Requests\LoginRequest;
use Saritasa\Laravel\Controllers\Responses\AuthSuccess;
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
     * @param JWTAuth $jwtAuth Jwt auth service
     * @param IDataTransformer $transformer default transformer to apply to handled entity.
     */
    public function __construct(JWTAuth $jwtAuth, IDataTransformer $transformer = null)
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
        } catch (JWTException $e) {
            $this->response->errorInternal(trans('controllers::auth.jwt_error'));
        }

        return $this->json(new AuthSuccess($token));
    }

    /**
     * Logout user.
     * Invalidate access token.
     *
     * @return Response
     */
    public function logout(): Response
    {
        $this->jwtAuth->invalidate();
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
            $newToken = $this->jwtAuth->refresh();
        } catch (JWTException $e) {
            $this->response->errorUnauthorized(trans('controllers::auth.jwt_refresh_error'));
        }

        return $this->json(new AuthSuccess($newToken));
    }
}
