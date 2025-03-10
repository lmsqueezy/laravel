<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionExpired;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionExpired
{
    use CanResolveBillable;

    /**
     * Handle the subscription expired event.
     *
     * @param array $payload
     * @return void
     */
    public function handle(array $payload): void
    {
        if (!$subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        if ($subscription->billable) {
            SubscriptionExpired::dispatch($subscription->billable, $subscription, $payload);
        }
    }
}
