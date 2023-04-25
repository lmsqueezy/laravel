<?php

namespace LemonSqueezy\Laravel\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Subscription;

trait ManagesSubscriptions
{
    /**
     * Get all of the subscriptions for the Billable model.
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(LemonSqueezy::$subscriptionModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Get a subscription instance by type.
     */
    public function subscription(string $type = 'default'): ?Subscription
    {
        return $this->subscriptions->where('type', $type)->first();
    }

    /**
     * Determine if the billable has a valid subscription.
     */
    public function subscribed(string $type = 'default', string $variant = null): bool
    {
        $subscription = $this->subscription($type);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $variant ? $subscription->hasVariant($variant) : true;
    }

    /**
     * Determine if the billable has a valid subscription for the given variant.
     */
    public function subscribedToVariant(string $variant, string $type = 'default'): bool
    {
        $subscription = $this->subscription($type);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $subscription->hasVariant($variant);
    }
}
