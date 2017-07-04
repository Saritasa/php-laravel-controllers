<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Response;
use Saritasa\Laravel\Controllers\Requests\LoginRequest;
use Saritasa\Laravel\Controllers\Responses\AuthSuccess;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * Authenticate API Controller. Uses JWT authentication
 */
class JWTAuthApiController extends BaseApiController
{

    /**
     * Authenticate user
     * Authenticate user by `email` and `password`.
     *
     * @param LoginRequest $request HTTP Request
     * @return Response
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                $this->response->errorNotFound(trans('controllers::auth.failed'));
            }
        } catch (JWTException $e) {
            $this->response->errorInternal(trans('controllers::auth.jwt_error'));
        }

        return $this->json(new AuthSuccess($token));
    }

    /**
     * Logout user
     *
     * Invalidate access token
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->response->noContent();
    }

    /**
     * Refresh the access token
     *
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::getToken();
            $newToken = JWTAuth::refresh($token);
            return $this->json(new AuthSuccess($newToken));
        } catch (JWTException $e) {
            $this->response->errorUnauthorized(trans('controllers::auth.jwt_refresh_error'));
        }
    }
}
