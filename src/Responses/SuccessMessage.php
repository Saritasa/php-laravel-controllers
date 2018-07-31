<?php

namespace Saritasa\LaravelControllers\Responses;

use Saritasa\Transformers\DtoModel;

/**
 * Success message for use in http responses.
 */
class SuccessMessage extends DtoModel
{
    /**
     * Success message text.
     *
     * @var string
     */
    protected $message;

    /**
     * Success message for use in http responses.
     *
     * @param string $message Success message test
     */
    public function __construct(string $message)
    {
        parent::__construct(['message' => $message]);
    }
}
