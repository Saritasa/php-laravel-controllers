<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

class AuthSuccess extends DtoModel {

    /**
     * Authentication token
     *
     * @var string
     */
    protected $token;

    public function __construct(string $token)
    {
        parent::__construct([ 'token' => $token]);
    }
}
