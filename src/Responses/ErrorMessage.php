<?php

namespace Saritasa\LaravelControllers\Responses;

use Saritasa\Transformers\DtoModel;

/**
 * Error message for use in http responses.
 */
class ErrorMessage extends DtoModel
{
    /**
     * Error message text.
     *
     * @var string
     */
    protected $message;

    /**
     * Error message for use in http responses.
     *
     * @param string $message Error message test
     */
    public function __construct(string $message)
    {
        parent::__construct(['message' => $message]);
    }
}
