<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks;

use InvalidArgumentException;
use LemonSqueezy\Laravel\Webhooks\Enums\Topic;
use LemonSqueezy\Laravel\Webhooks\Hooks\Affiliate;
use LemonSqueezy\Laravel\Webhooks\Hooks\LicenseKey;
use LemonSqueezy\Laravel\Webhooks\Hooks\Meta;
use LemonSqueezy\Laravel\Webhooks\Hooks\Order;
use LemonSqueezy\Laravel\Webhooks\Hooks\Subscription;
use LemonSqueezy\Laravel\Webhooks\Hooks\Webhook;

class Factory
{
    public static function build(Topic $topic, array $payload): Webhook
    {
        $attributes = match ($topic) {
            Topic::OrderCreated,
            Topic::OrderRefunded => Order::fromArray($payload['attributes']),
            Topic::SubscriptionCreated,
            Topic::SubscriptionUpdated,
            Topic::SubscriptionCancelled,
            Topic::SubscriptionResumed,
            Topic::SubscriptionExpired,
            Topic::SubscriptionPaused,
            Topic::SubscriptionUnpaused,
            Topic::SubscriptionPaymentFailed,
            Topic::SubscriptionPaymentSuccess,
            Topic::SubscriptionPaymentRecovered,
            Topic::SubscriptionPaymentRefunded => Subscription::fromArray($payload['attributes']),
            Topic::LicenseKeyCreated,
            Topic::LicenseKeyUpdated => LicenseKey::fromArray($payload['attributes']),
            Topic::AffiliateActivated => Affiliate::fromArray($payload['attributes']),
            default => throw new InvalidArgumentException(
                message: "Unsupported topic: {$topic->value}.",
            ),
        };

        return new Webhook(
            meta: new Meta(
                event_name: $topic->value,
            ),
            type: $payload['type'],
            id: $payload['id'],
            attributes: $attributes,
        );
    }
}
