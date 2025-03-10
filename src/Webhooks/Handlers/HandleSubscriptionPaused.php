<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionPaused;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionPaused
{
    use CanResolveBillable;

    /**
     * Handle the subscription paused event.
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

        SubscriptionPaused::dispatch($subscription->billable, $subscription, $payload);
    }
}
