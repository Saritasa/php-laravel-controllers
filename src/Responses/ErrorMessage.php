<?php

namespace Saritasa\LaravelControllers\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Saritasa\Dto;

/**
 * Error message for use in responses.
 */
class ErrorMessage extends Dto implements Arrayable
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
