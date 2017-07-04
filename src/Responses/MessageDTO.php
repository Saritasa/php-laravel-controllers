<?php

namespace Saritasa\Laravel\Controllers\Responses;

use Saritasa\Transformers\DtoModel;

/**
 * Message DTO class.
 * @deprecated Use more specific ErrorMessageDTO or SuccessMessageDTO class
 * @see ErrorMessage, SuccessMessage
 */
class MessageDTO extends DtoModel {

    /**
     * Message text
     *
     * @var string
     */
    protected $message;

    /**
     * Message DTO class.
     * @deprecated Use more specific ErrorMessageDTO or SuccessMessageDTO class
     * @see ErrorMessage, SuccessMessage
     */
    public function __construct(string $message)
    {
        parent::__construct([ 'message' => $message]);
    }
}
