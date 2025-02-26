<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';

    case Failed = 'failed';

    case Paid = 'paid';

    case Refunded = 'refunded';

    case PartialRefund = 'partial_refund';

    case Fraudulent = 'fraudulent';
}
