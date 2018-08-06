<?php

namespace Saritasa\LaravelControllers\Responses;

use Saritasa\Dto;

/**
 * Auth success response message.
 */
class AuthSuccess extends Dto
{
    /**
     * Authentication token
     *
     * @var string
     */
    protected $token;

    /**
     * Auth success response message.
     *
     * @param string $token User token
     */
    public function __construct(string $token)
    {
        parent::__construct(['token' => $token]);
    }
}
