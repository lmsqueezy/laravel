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
}
