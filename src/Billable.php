<?php

namespace LemonSqueezy\Laravel;

use LemonSqueezy\Laravel\Concerns\ManagesCheckouts;
use LemonSqueezy\Laravel\Concerns\ManagesCustomer;
use LemonSqueezy\Laravel\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCustomer;
    use ManagesCheckouts;
    use ManagesSubscriptions;
}
