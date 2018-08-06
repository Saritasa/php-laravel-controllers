<?php

namespace Saritasa\LaravelControllers\Responses;

use Saritasa\Dto;

/**
 * Error message for use in responses.
 */
class ErrorMessage extends Dto
{
    /**
     * Error message text.
     *
     * @var string
     */
    protected $message;

    /**
     * Error message for use in responses.
     *
     * @param string $message Error message text
     */
    public function __construct(string $message)
    {
        parent::__construct(['message' => $message]);
    }
}
