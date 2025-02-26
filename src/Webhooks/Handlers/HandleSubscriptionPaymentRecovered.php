<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionPaymentRecovered;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionPaymentRecovered
{
    use CanResolveBillable;

    /**
     * Handle the subscription payment recovered event.
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        if (
            ($subscription = $this->findSubscription($payload['data']['attributes']['subscription_id'])) &&
            $subscription->billable
        ) {
            SubscriptionPaymentRecovered::dispatch($subscription->billable, $subscription, $payload);
        }
    }
}
