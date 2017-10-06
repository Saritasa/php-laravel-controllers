<?php
namespace Saritasa\Laravel\Controllers\Services;

use Saritasa\Exceptions\ServiceException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * User authentication service. Using JTW.
 */
class AuthJWTService
{
    /**
     * Authenticate User with JWT
     *
     * @param string $email user email
     * @param string $password user password
     * @return string JWT token
     * @throws ServiceException
     * @throws ModelNotFoundException
     */
    public function auth(string $email, string $password)
    {
        try {
            if (!$token = JWTAuth::attempt(compact('email', 'password'))) {
                throw new ModelNotFoundException('Invalid email or password');
            }
        } catch (JWTException $e) {
            throw new ServiceException('Could not create token', 0, $e);
        }
        return $token;
    }

    /**
     * Log out the user that logged in with JWT
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }

    /**
     * Refresh the JWT access token
     *
     * @return string new JWT token
     * @throws ServiceException
     */
    public function refreshToken()
    {
        try {
            $token = JWTAuth::getToken();
            return JWTAuth::refresh($token);
        } catch (TokenInvalidException $e) {
            throw new ServiceException('The token is invalid', 0, $e);
        } catch (JWTException $e) {
            throw new ServiceException('Could not refresh token', 0, $e);
        }
    }

    /**
     * Get current authenticated user
     *
     * @return \App\Models\User
     */
    public function getUser()
    {
        return JWTAuth::parseToken()->authenticate();
    }
}
