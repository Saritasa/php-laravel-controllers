<?php

namespace Saritasa\LaravelControllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Saritasa\LaravelControllers\Requests\Concerns\ILoginRequest;
use Saritasa\LaravelControllers\Responses\AuthSuccess;
use Saritasa\LaravelControllers\Responses\ResponsesTrait;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\JWTAuth;

/**
 * Authenticate API Controller. Uses JWT authentication.
 */
class JWTAuthApiController extends Controller
{
    use ResponsesTrait;

    /**
     * Jwt auth service.
     *
     * @var JWTAuth
     */
    protected $jwtAuth;

    /**
     * Authenticate API Controller. Uses JWT authentication.
     */
    public function __construct(JWTAuth $jwtAuth)
    {
        $this->jwtAuth = $jwtAuth;
    }

    /**
     * Authenticate user by `email` and `password`.
     *
     * @param ILoginRequest $request HTTP Request
     *
     * @return JsonResponse
     *
     * @throws HttpException
     */
    public function login(ILoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = $this->jwtAuth->attempt($credentials)) {
                return $this->errorNotFound(trans('controllers::auth.failed'));
            }

            return new JsonResponse(new AuthSuccess($token));
        } catch (JWTException $e) {
            return $this->errorInternal(trans('controllers::auth.jwt_error'));
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
            $this->jwtAuth->invalidate();
        } catch (JWTException $exception) {
            $this->errorUnauthorized(trans('controllers::auth.jwt_blacklist_error'));
        }
        return $this->responseNoContent();
    }

    /**
     * Refresh the access token.
     *
     * @return Response
     *
     * @throws HttpException
     */
    public function refreshToken(): JsonResponse
    {
        try {
            return $this->json(new AuthSuccess($this->jwtAuth->refresh()));
        } catch (JWTException $e) {
            return $this->errorForbidden(trans('controllers::auth.jwt_refresh_error'));
        }
    }
}
