<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use LaravelLemonSqueezy\Billable;

class User extends Model
{
    use Billable;

    public function getKey()
    {
        return 'user_123';
    }

    public function getMorphClass()
    {
        return 'users';
    }
}
