<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\SubscriptionCancelled;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionCancelled
{
    use CanResolveBillable;

    /**
     * Handle the subscription cancelled event.
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
            SubscriptionCancelled::dispatch($subscription->billable, $subscription, $payload);
        }
    }
}
