<?php

namespace Saritasa\LaravelControllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Str;
use Saritasa\LaravelControllers\Responses\ErrorMessage;
use Saritasa\LaravelControllers\Responses\SuccessMessage;

/**
 * This controller is responsible for handling password reset requests
 * Utilize native Laravel password management without UI, in API style
 * https://laravel.com/docs/passwords
 */
class ResetPasswordApiController extends BaseApiController
{
    use ResetsPasswords;

    /**
     * Get the response for a successful password reset.
     *
     * @param Request $request HTTP Request instance
     * @param string $response ID of language resource to use as response
     *
     * @return Response
     */
    protected function sendResetResponse(Request $request, string $response): Response
    {
        return $this->json(new SuccessMessage(trans($response)));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param Request $request HTTP Request instance
     * @param string $message Response text
     *
     * @return Response
     */
    protected function sendResetFailedResponse(Request $request, $message): Response
    {
        return $this->json(new ErrorMessage(trans($message)));
    }

    /**
     * Reset the given user's password.
     *
     * @param Model|Authenticatable $user User, who wants to reset password
     * @param string $password New Password
     *
     * @return void
     */
    protected function resetPassword($user, $password): void
    {
        $user->forceFill(['password' => $password, 'remember_token' => Str::random(60),])->save();
    }
}
