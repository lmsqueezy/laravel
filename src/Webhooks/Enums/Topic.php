<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Enums;

/**
 * @see https://docs.lemonsqueezy.com/guides/developer-guide/webhooks
 */
enum Topic: string
{
    // Occurs when a new order is successfully placed.
    case OrderCreated = 'order_created';

    // Occurs when a full or partial refund is made on an order.
    case OrderRefunded = 'order_refunded';

    // Occurs when a new subscription is successfully created. An order_created event will always be sent alongside a subscription_created event.
    case SubscriptionCreated = 'subscription_created';

    // Occurs when a subscription’s data is changed or updated. This event can be used as a “catch-all” to make sure you always have access to the latest subscription data.
    case SubscriptionUpdated = 'subscription_updated';

    // 	Occurs when a subscription is cancelled manually by the customer or store owner. The subscription enters a “grace period” until the next billing date, when it will expire. It is possible for the subscription to be resumed during this period.
    case SubscriptionCancelled = 'subscription_cancelled';

    // Occurs when a subscription is resumed after being previously cancelled.
    case SubscriptionResumed = 'subscription_resumed';

    // Occurs when a subscription has ended after being previously cancelled, or once dunning has been completed for past_due subscriptions. You can manage how long to wait before the system marks delinquent subscriptions as expired.
    case SubscriptionExpired = 'subscription_expired';

    // Occurs when a subscription’s payment collection is paused.
    case SubscriptionPaused = 'subscription_paused';

    // Occurs when a subscription’s payment collection is resumed after being previously paused.
    case SubscriptionUnpaused = 'subscription_unpaused';

    // Occurs when a subscription renewal payment fails.
    case SubscriptionPaymentFailed = 'subscription_payment_failed';

    // Occurs when a subscription payment is successful.
    case SubscriptionPaymentSuccess = 'subscription_payment_success';

    // Occurs when a subscription has a successful payment after a failed payment. A subscription_payment_success event will always be sent alongside a subscription_payment_recovered event.
    case SubscriptionPaymentRecovered = 'subscription_payment_recovered';

    // Occurs when a subscription payment is refunded.
    case SubscriptionPaymentRefunded = 'subscription_payment_refunded';

    // Occurs when a license key is created from a new order. An order_created event will always be sent alongside a license_key_created event.
    case LicenseKeyCreated = 'license_key_created';

    // Occurs when a license key is updated.
    case LicenseKeyUpdated = 'license_key_updated';

    // Occurs when an affiliate is activated.
    case AffiliateActivated = 'affiliate_activated';
}
