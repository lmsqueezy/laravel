<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;
use Illuminate\Console\Command;

class ListenException extends Exception
{
    public static function notLocalEnvironment(): static
    {
        return new static('lmsqueezy:listen can only be used in local environment.', Command::FAILURE);
    }

    public static function noWebhooksFound(): static
    {
        return new static('No webhooks found to clean.', Command::SUCCESS);
    }

    public static function usingTestService(): static
    {
        return new static('lmsqueezy:listen is using the test service.', Command::SUCCESS);
    }
}
