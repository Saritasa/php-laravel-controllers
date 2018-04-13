<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

/**
 * Message for use in http responses.
 */
class Message extends DtoModel
{
    /**
     * Message text
     *
     * @var string
     */
    protected $message;

    /**
     * Message for use in http responses.
     *
     * @param string $message Message text
     */
    public function __construct(string $message)
    {
        parent::__construct(['message' => $message]);
    }
}
