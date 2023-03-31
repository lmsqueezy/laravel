<?php

namespace LaravelLemonSqueezy\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use LaravelLemonSqueezy\Events\SubscriptionCancelled;
use LaravelLemonSqueezy\Events\SubscriptionCreated;
use LaravelLemonSqueezy\Events\SubscriptionExpired;
use LaravelLemonSqueezy\Events\SubscriptionPaused;
use LaravelLemonSqueezy\Events\SubscriptionResumed;
use LaravelLemonSqueezy\Events\SubscriptionUnpaused;
use LaravelLemonSqueezy\Events\SubscriptionUpdated;
use LaravelLemonSqueezy\Events\WebhookHandled;
use LaravelLemonSqueezy\Events\WebhookReceived;
use LaravelLemonSqueezy\Exceptions\InvalidCustomPayload;
use LaravelLemonSqueezy\Http\Middleware\VerifyWebhookSignature;
use LaravelLemonSqueezy\LemonSqueezy;
use LaravelLemonSqueezy\Subscription;
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

        if (! isset($custom) || ! is_array($custom) || ! isset($custom['subscription_type'])) {
            throw new InvalidCustomPayload;
        }

        $attributes = $payload['data']['attributes'];

        $billable = $this->findOrCreateCustomer((string) $attributes['customer_id'], $custom);

        $subscription = $billable->subscriptions()->create([
            'type' => $custom['subscription_type'],
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
     * @return \LaravelLemonSqueezy\Billable
     *
     * @throws InvalidCustomPayload
     */
    protected function findOrCreateCustomer(string $customerId, array $custom)
    {
        if (! isset($custom['billable_id'], $custom['billable_type'])) {
            throw new InvalidCustomPayload;
        }

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
