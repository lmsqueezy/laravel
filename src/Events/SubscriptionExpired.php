<?php

namespace LaravelLemonSqueezy\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LaravelLemonSqueezy\Subscription;

class SubscriptionExpired
{
    use Dispatchable, SerializesModels;

    /**
     * The billable entity.
     */
    public Model $billable;

    /**
     * The subscription instance.
     */
    public Subscription $subscription;

    /**
     * The payload array.
     */
    public array $payload;

    public function __construct(Model $billable, Subscription $subscription, array $payload)
    {
        $this->billable = $billable;
        $this->subscription = $subscription;
        $this->payload = $payload;
    }
}
