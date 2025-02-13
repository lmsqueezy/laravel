<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum PauseMode: string
{
    // If you can’t offer your services for a period of time (for maintenance as an example), you can void invoices so your customers aren’t charged.
    case Void = 'void';

    // Offer your subscription services for free, whilst halting payment collection.
    case Free = 'free';
}
