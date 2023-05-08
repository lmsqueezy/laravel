<?php

namespace LemonSqueezy\Laravel\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Subscription;

trait ManagesSubscriptions
{
    /**
     * Get all of the subscriptions for the billable.
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(LemonSqueezy::$subscriptionModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Get a subscription instance by type.
     */
    public function subscription(string $type = Subscription::DEFAULT_TYPE): ?Subscription
    {
        return $this->subscriptions->where('type', $type)->first();
    }

    /**
     * Determine if the billable is on trial.
     */
    public function onTrial(string $type = Subscription::DEFAULT_TYPE, string $variant = null): bool
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($type);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return $variant ? $subscription->hasVariant($variant) : true;
    }

    /**
     * Determine if the billable's trial has ended.
     */
    public function hasExpiredTrial(string $type = Subscription::DEFAULT_TYPE, string $variant = null): bool
    {
        if (func_num_args() === 0 && $this->hasExpiredGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($type);

        if (! $subscription || ! $subscription->hasExpiredTrial()) {
            return false;
        }

        return $variant ? $subscription->hasPlan($variant) : true;
    }

    /**
     * Determine if the billable is on a "generic" trial at the model level.
     */
    public function onGenericTrial(): bool
    {
        if (is_null($this->customer)) {
            return false;
        }

        return $this->customer->onGenericTrial();
    }

    /**
     * Determine if the billable's "generic" trial at the model level has expired.
     */
    public function hasExpiredGenericTrial(): bool
    {
        if (is_null($this->customer)) {
            return false;
        }

        return $this->customer->hasExpiredGenericTrial();
    }

    /**
     * Get the ending date of the trial.
     */
    public function trialEndsAt(string $type = Subscription::DEFAULT_TYPE): ?Carbon
    {
        if ($subscription = $this->subscription($type)) {
            return $subscription->trial_ends_at;
        }

        return $this->customer->trial_ends_at;
    }

    /**
     * Determine if the billable has a valid subscription.
     */
    public function subscribed(string $type = Subscription::DEFAULT_TYPE, string $variant = null): bool
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
    public function subscribedToVariant(string $variant, string $type = Subscription::DEFAULT_TYPE): bool
    {
        $subscription = $this->subscription($type);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $subscription->hasVariant($variant);
    }
}
