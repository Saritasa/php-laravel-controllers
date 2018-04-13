<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

/**
 * Auth success response message.
 */
class AuthSuccess extends DtoModel
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
