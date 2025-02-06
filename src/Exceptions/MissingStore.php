<?php

namespace LemonSqueezy\Laravel\Exceptions;

use Exception;

class MissingStore extends Exception
{
    public static function notConfigured(): MissingStore
    {
        return new MissingStore('The Lemon Squeezy store was not configured.');
    }
}
