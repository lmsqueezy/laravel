<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

enum SubscriptionInvoiceStatus: string
{
    // The invoice is waiting for payment.
    case Pending = 'pending';

    // The invoice has been paid.
    case Paid = 'paid';

    // The invoice was cancelled or cannot be paid.
    case Void = 'void';

    // The invoice was paid but has since been fully refunded.
    case Refunded = 'refunded';

    // The invoice was paid but has since been partially refunded.
    case PartialRefunded = 'partial_refunded';
}
