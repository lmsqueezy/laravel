<?php

namespace LemonSqueezy\Laravel;

use LemonSqueezy\Laravel\Concerns\ManagesCheckouts;
use LemonSqueezy\Laravel\Concerns\ManagesCustomer;
use LemonSqueezy\Laravel\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCheckouts;
    use ManagesCustomer;
    use ManagesSubscriptions;
}
