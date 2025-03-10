<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionPaymentFailed;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionPaymentFailed
{
    use CanResolveBillable;

    /**
     * Handle the subscription payment failed event.
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
            SubscriptionPaymentFailed::dispatch($subscription->billable, $subscription, $payload);
        }
    }
}
