<?php

namespace LemonSqueezy\Laravel\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use LemonSqueezy\Laravel\Events\SubscriptionCancelled;
use LemonSqueezy\Laravel\Events\SubscriptionCreated;
use LemonSqueezy\Laravel\Events\SubscriptionExpired;
use LemonSqueezy\Laravel\Events\SubscriptionPaused;
use LemonSqueezy\Laravel\Events\SubscriptionResumed;
use LemonSqueezy\Laravel\Events\SubscriptionUnpaused;
use LemonSqueezy\Laravel\Events\SubscriptionUpdated;
use LemonSqueezy\Laravel\Events\WebhookHandled;
use LemonSqueezy\Laravel\Events\WebhookReceived;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\Http\Middleware\VerifyWebhookSignature;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Subscription;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    public function __construct()
    {
        if (config('lemon-squeezy.signing_secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle a Lemon Squeezy webhook call.
     */
    public function __invoke(Request $request): Response
    {
        $payload = $request->all();

        if (! isset($payload['meta']['event_name'])) {
            return new Response('Webhook received but no event name was found.');
        }

        $method = 'handle'.Str::studly($payload['meta']['event_name']);

        WebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            try {
                $this->{$method}($payload);
            } catch (InvalidCustomPayload $e) {
                return new Response('Webhook skipped due to invalid custom data.');
            }

            WebhookHandled::dispatch($payload);

            return new Response('Webhook was handled.');
        }

        return new Response('Webhook received but no handler found.');
    }

    /**
     * @throws InvalidCustomPayload
     */
    public function handleSubscriptionCreated(array $payload): void
    {
        $custom = $payload['meta']['custom_data'] ?? null;

        if (! isset($custom) || ! is_array($custom) || ! isset($custom['billable_id'], $custom['billable_type'])) {
            throw new InvalidCustomPayload;
        }

        $attributes = $payload['data']['attributes'];

        $billable = $this->findOrCreateCustomer((string) $attributes['customer_id'], $custom);

        $subscription = $billable->subscriptions()->create([
            'type' => $custom['subscription_type'] ?? Subscription::DEFAULT_TYPE,
            'lemon_squeezy_id' => $payload['data']['id'],
            'status' => $attributes['status'],
            'product_id' => $attributes['product_id'],
            'variant_id' => $attributes['variant_id'],
            'card_brand' => $attributes['card_brand'] ?? null,
            'card_last_four' => $attributes['card_last_four'] ?? null,
            'trial_ends_at' => $attributes['trial_ends_at'] ? Carbon::make($attributes['trial_ends_at']) : null,
            'renews_at' => $attributes['renews_at'] ? Carbon::make($attributes['renews_at']) : null,
            'ends_at' => $attributes['ends_at'] ? Carbon::make($attributes['ends_at']) : null,
        ]);

        // Terminate the billable's generic trial at the model level if it exists...
        if (! is_null($billable->customer->trial_ends_at)) {
            $billable->customer->update(['trial_ends_at' => null]);
        }

        SubscriptionCreated::dispatch($billable, $subscription, $payload);
    }

    protected function handleSubscriptionUpdated(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionUpdated::dispatch($subscription->billable, $subscription, $payload);
    }

    protected function handleSubscriptionCancelled(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionCancelled::dispatch($subscription->billable, $subscription, $payload);
    }

    protected function handleSubscriptionResumed(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionResumed::dispatch($subscription->billable, $subscription, $payload);
    }

    protected function handleSubscriptionExpired(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionExpired::dispatch($subscription->billable, $subscription, $payload);
    }

    protected function handleSubscriptionPaused(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionPaused::dispatch($subscription->billable, $subscription, $payload);
    }

    protected function handleSubscriptionUnpaused(array $payload): void
    {
        if (! $subscription = $this->findSubscription($payload['data']['id'])) {
            return;
        }

        $subscription = $subscription->sync($payload['data']['attributes']);

        SubscriptionUnpaused::dispatch($subscription->billable, $subscription, $payload);
    }

    /**
     * @return \LemonSqueezy\Laravel\Billable
     */
    protected function findOrCreateCustomer(string $customerId, array $custom)
    {
        return LemonSqueezy::$customerModel::firstOrCreate([
            'lemon_squeezy_id' => $customerId,
        ], [
            'billable_id' => $custom['billable_id'],
            'billable_type' => $custom['billable_type'],
        ])->billable;
    }

    protected function findSubscription(string $subscriptionId): ?Subscription
    {
        return LemonSqueezy::$subscriptionModel::firstWhere('lemon_squeezy_id', $subscriptionId);
    }
}
