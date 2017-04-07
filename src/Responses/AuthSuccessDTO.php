<?php

namespace Saritasa\Laravel\Controllers\Responses;

class AuthSuccessDTO extends DtoModel {

    /**
     * Authentication token
     *
     * @var string
     */
    protected $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }
}