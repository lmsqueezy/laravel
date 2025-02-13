<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;

class ReservedCustomKeys extends Exception
{
    public static function overwriteAttempt(): ReservedCustomKeys
    {
        return new ReservedCustomKeys(
            'You cannot use "billable_id", "billable_type" or "subscription_type" as custom data keys because these are reserved keywords.'
        );
    }
}
