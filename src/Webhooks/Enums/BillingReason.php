<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum BillingReason: string
{
    // The initial invoice generated when the subscription is created.
    case Initial = 'initial';

    // A renewal invoice generated when the subscription is renewed.
    case Renewal = 'renewal';

    // An invoice generated when the subscription is updated.
    case Updated = 'updated';
}
