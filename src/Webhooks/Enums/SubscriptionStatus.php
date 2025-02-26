<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum SubscriptionStatus: string
{
    case OnTrial = 'on_trial';

    case Active = 'active';

    case Paused = 'paused';

    case PastDue = 'past_due';

    case Unpaid = 'unpaid';

    case Cancelled = 'cancelled';

    case Expired = 'expired';
}
