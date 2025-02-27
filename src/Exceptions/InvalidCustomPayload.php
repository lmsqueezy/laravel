<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;
use LemonSqueezy\Laravel\Http\Throwable\BadRequest;

class InvalidCustomPayload extends Exception implements BadRequest
{

    public function __construct(string $message = '')
    {
        parent::__construct($message ?? 'Invalid custom data');
    }
}
