<?php

namespace LemonSqueezy\Laravel\Exceptions;

use LemonSqueezy\Laravel\Http\Throwable\BadRequest;
class LicenseKeyNotValidated extends \Exception implements BadRequest
{
    public static function withErrorMessage(string $message): static {
        return new self("The license key couldn't be validated: {$message}");
    }
}
