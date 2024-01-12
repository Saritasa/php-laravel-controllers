<?php

namespace Saritasa\LaravelControllers\Api;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Saritasa\LaravelControllers\Responses\SuccessMessage;

/**
 * This controller is responsible for handling password reset emails
 *
 * Utilize native Laravel password management without UI, in API style
 * https://laravel.com/docs/5.4/passwords
 */
class ForgotPasswordApiController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param Request $request HTTP Request
     * @param string $languageResourceId Resource ID of message to display to user
     *
     * @return Response
     */
    protected function sendResetLinkResponse(Request $request, $languageResourceId): Response
    {
        return new JsonResponse(new SuccessMessage(trans($languageResourceId)));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param Request $request HTTP Request
     * @param string $languageResourceId Resource ID of message to display to user
     *
     * @return void
     */
    protected function sendResetLinkFailedResponse(Request $request, $languageResourceId): void
    {
        $this->response->errorNotFound(trans($languageResourceId));
    }
}
