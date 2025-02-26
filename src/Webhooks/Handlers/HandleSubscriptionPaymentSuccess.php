<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionPaymentSuccess;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionPaymentSuccess
{
    use CanResolveBillable;

    /**
     * Handle the subscription payment success event.
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
            SubscriptionPaymentSuccess::dispatch($subscription->billable, $subscription, $payload);
        }
    }
}
