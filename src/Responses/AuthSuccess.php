<?php

namespace Saritasa\LaravelControllers\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Saritasa\Dto;

/**
 * Auth success response message.
 */
class AuthSuccess extends Dto implements Arrayable
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
