<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Response;
use Saritasa\Exceptions\ServiceException;
use Saritasa\Laravel\Controllers\Requests\LoginRequest;
use Saritasa\Laravel\Controllers\Responses\AuthSuccessDTO;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * Authenticate API Controller
 */
class JWTAuthApiController extends BaseApiController
{

    /**
     * Authenticate user
     * Authenticate user by `email` and `password`.
     *
     * @param LoginRequest $request HTTP Request
     * @return Response
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new NotFoundHttpException(trans('auth.failed'));
            }
        } catch (JWTException $e) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, trans('auth.jwt_error'));
        }

        return $this->json(new AuthSuccessDTO($token));
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
     * @throws ServiceException
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::getToken();
            $newToken = JWTAuth::refresh($token);
            return $this->json(new AuthSuccessDTO($newToken));
        } catch (JWTException $e) {
            throw new ServiceException(trans('auth.jwt_refresh_error'), 0, $e);
        }
    }
}