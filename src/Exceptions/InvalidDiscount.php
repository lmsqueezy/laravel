<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidDiscount extends Exception
{
    /**
     * Create an instance for an invalid discount code format.
     *
     * @param string $code
     * @return static
     */
    public static function invalidFormat(string $code): self
    {
        return new static("Invalid discount code format: '{$code}'.");
    }

    /**
     * Create an instance for a non-applicable discount.
     *
     * @param string $code
     * @return static
     */
    public static function notApplicable(string $code): self
    {
        return new static("Discount code '{$code}' is not applicable.");
    }
}
