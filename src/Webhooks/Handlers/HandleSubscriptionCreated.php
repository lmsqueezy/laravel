<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use Carbon\Carbon;
use LemonSqueezy\Laravel\Events\SubscriptionCreated;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\Subscription;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleSubscriptionCreated
{
    use CanResolveBillable;

    /**
     * Handle the subscription created event.
     *
     * @param array $payload
     * @return void
     * @throws InvalidCustomPayload
     */
    public function handle(array $payload): void
    {
        $custom = $payload['meta']['custom_data'] ?? null;
        $attributes = $payload['data']['attributes'];

        $billable = $this->resolveBillable($payload);

        $subscription = $billable->subscriptions()->create([
            'type' => $custom['subscription_type'] ?? Subscription::DEFAULT_TYPE,
            'lemon_squeezy_id' => $payload['data']['id'],
            'status' => $attributes['status'],
            'product_id' => (string) $attributes['product_id'],
            'variant_id' => (string) $attributes['variant_id'],
            'card_brand' => $attributes['card_brand'] ?? null,
            'card_last_four' => $attributes['card_last_four'] ?? null,
            'trial_ends_at' => $attributes['trial_ends_at'] ? Carbon::make($attributes['trial_ends_at']) : null,
            'renews_at' => $attributes['renews_at'] ? Carbon::make($attributes['renews_at']) : null,
            'ends_at' => $attributes['ends_at'] ? Carbon::make($attributes['ends_at']) : null,
        ]);

        // Terminate the billables generic trial at the model level if it exists...
        if (!is_null($billable->customer->trial_ends_at)) {
            $billable->customer->update(['trial_ends_at' => null]);
        }

        // Set the billables lemon squeezy id if it was on generic trial at the model level
        if (is_null($billable->customer->lemon_squeezy_id)) {
            $billable->customer->update(['lemon_squeezy_id' => $attributes['customer_id']]);
        }

        SubscriptionCreated::dispatch($billable, $subscription, $payload);
    }
}
