<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum AffiliateStatus: string
{
    case Active = 'active';

    case Pending = 'pending';

    case Disabled = 'disabled';
}
