<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Dingo\Api\Http\Request;
use Illuminate\Support\Str;
use Saritasa\Laravel\Controllers\Responses\Message;

/**
 * This controller is responsible for handling password reset requests
 * Utilize native Laravel password management without UI, in API style
 * https://laravel.com/docs/5.4/passwords
 */
class ResetPasswordApiController extends BaseApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Get the response for a successful password reset.
     *
     * @param string  $response ID of language resource to send as response
     * @return Response
     */
    protected function sendResetResponse($response)
    {
        return $this->json(new Message(trans($response)));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param Request $request HTTP Request instance
     * @param string $message Response text
     *
     * @return Response
     */
    protected function sendResetFailedResponse(Request $request, $message)
    {
        return $this->json(new Message($message));
    }

    /**
     * Reset the given user's password.
     *
     * @param Model|Authenticatable $user User, who wants to reset password
     * @param string $password New Password
     *
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->forceFill([
            'password' => $password,
            'remember_token' => Str::random(60),
        ])->save();
    }
}
