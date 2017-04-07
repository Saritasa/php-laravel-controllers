<?php

namespace Saritasa\Laravel\Controllers\Api;

use App\Api\V1\Responses\AuthSuccessDTO;
use App\Exceptions\ModelException;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;


/**
 * Authenticate resource representation.
 */
class AuthenticateApiController extends BaseApiController
{
    /**
     * Authenticate user
     * Authenticate user by `email` and `password`.
     *
     * @param Request $request HTTP Request
     * @return Response
     */
    public function login(Request $request)
    {
        $credentials = $this->getValidatedCredentials($request, 'email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                throw new NotFoundHttpException(trans('auth.failed'));
            }
        } catch (JWTException $e) {
            throw new ModelException(Response::HTTP_INTERNAL_SERVER_ERROR, trans('auth.jwt_error'));
        }

        return $this->json(new AuthSuccessDTO($token));
    }

    /**
     * Extract from request primary user identifier, password, and validate them against basic rules
     *
     * @param Request $request
     * @param $user_id_field
     * @param $password_field
     * @throws ValidationException
     * @return array
     */
    private function getValidatedCredentials(Request $request, $user_id_field, $password_field): array
    {
        $credentials = $request->only($user_id_field, $password_field);
        $this->validate($request, [
            $user_id_field => 'required',
            $password_field => 'required'
        ]);
        return $credentials;
    }


    /**
     * Logout user
     * Invalidate access token
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return $this->response->noContent();
    }
}