<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

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