<?php

namespace Saritasa\LaravelControllers\Responses;

use Illuminate\Contracts\Support\Arrayable;
use Saritasa\Transformers\DtoModel;

/**
 * Success message for use in responses.
 */
class SuccessMessage extends DtoModel implements Arrayable
{
    /**
     * Success message text.
     *
     * @var string
     */
    protected $message;

    /**
     * Success message for use in responses.
     *
     * @param string $message Success message text
     */
    public function __construct(string $message)
    {
        parent::__construct(['message' => $message]);
    }
}
