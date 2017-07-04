<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

class ErrorMessage extends DtoModel
{
    /**
     * Error message text
     *
     * @var string
     */
    protected $message;

    public function __construct(string $errorMessage)
    {
        parent::__construct(['message' => $errorMessage]);
    }
}
