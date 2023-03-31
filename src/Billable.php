<?php

namespace LaravelLemonSqueezy;

use LaravelLemonSqueezy\Concerns\ManagesCheckouts;
use LaravelLemonSqueezy\Concerns\ManagesCustomer;
use LaravelLemonSqueezy\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCustomer;
    use ManagesCheckouts;
    use ManagesSubscriptions;
}
