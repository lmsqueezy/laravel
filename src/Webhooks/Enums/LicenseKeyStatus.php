<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum LicenseKeyStatus: string
{
    case Inactive = 'inactive';

    case Active = 'active';

    case Expired = 'expired';

    case Disabled = 'disabled';
}
