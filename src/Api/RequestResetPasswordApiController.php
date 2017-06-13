<?php

namespace Saritasa\Laravel\Controllers\Api;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\JsonResponse;
use Saritasa\Laravel\Controllers\Responses\MessageDTO;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * This controller is responsible for handling password reset requests
 */
class RequestResetPasswordApiController extends BaseApiController
{
    use SendsPasswordResetEmails;

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $languageResourceId Resource ID of message to display to user
     * @return JsonResponse
     */
    public function sendResetLinkResponse($languageResourceId)
    {
        return new JsonResponse(new MessageDTO(trans($languageResourceId)));
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  Request
     * @param  string  $languageResourceId Resource ID of message to display to user
     * @return void

     * @throws HttpException
     */
    protected function sendResetLinkFailedResponse($request, $languageResourceId)
    {
        $this->response->errorNotFound(trans($languageResourceId));
    }
}