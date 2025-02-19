<?php

namespace LemonSqueezy\Laravel;

use LemonSqueezy\Laravel\Concerns\ManagesCheckouts;
use LemonSqueezy\Laravel\Concerns\ManagesCustomer;
use LemonSqueezy\Laravel\Concerns\ManagesLicenses;
use LemonSqueezy\Laravel\Concerns\ManagesOrders;
use LemonSqueezy\Laravel\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesCheckouts;
    use ManagesCustomer;
    use ManagesLicenses;
    use ManagesOrders;
    use ManagesSubscriptions;
}
