<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;
use LemonSqueezy\Laravel\Http\Throwable\BadRequest;

class ReservedCustomKeys extends Exception implements BadRequest
{
    public static function overwriteAttempt(): ReservedCustomKeys
    {
        return new ReservedCustomKeys(
            'You cannot use "billable_id", "billable_type" or "subscription_type" as custom data keys because these are reserved keywords.',
        );
    }
}
