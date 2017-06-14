<?php

namespace Saritasa\Laravel\Controllers\Api;

use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Saritasa\Laravel\Controllers\Responses\MessageDTO;

/**
 * This controller is responsible for handling password reset emails
 *
 * Utilize native Laravel password management without UI, in API style
 * https://laravel.com/docs/5.4/passwords
 */
class ForgotPasswordApiController extends BaseApiController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $languageResourceId Resource ID of message to display to user
     * @return Response
     */
    protected function sendResetLinkResponse($languageResourceId)
    {
        return $this->json(new MessageDTO(trans($languageResourceId)));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  Request $request
     * @param  string  $languageResourceId Resource ID of message to display to user
     * @return void
     */
    protected function sendResetLinkFailedResponse($request, $languageResourceId)
    {
        $this->response->errorNotFound(trans($languageResourceId));
    }
}
