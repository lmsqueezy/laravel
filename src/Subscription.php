<?php

namespace LemonSqueezy\Laravel;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LemonSqueezy\Laravel\Concerns\Prorates;
use LemonSqueezy\Laravel\Database\Factories\SubscriptionFactory;
use LogicException;

/**
 * @property \LemonSqueezy\Laravel\Billable $billable
 */
class Subscription extends Model
{
    use HasFactory;
    use Prorates;

    const STATUS_ON_TRIAL = 'on_trial';

    const STATUS_ACTIVE = 'active';

    const STATUS_PAUSED = 'paused';

    const STATUS_PAST_DUE = 'past_due';

    const STATUS_UNPAID = 'unpaid';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_EXPIRED = 'expired';

    const DEFAULT_TYPE = 'default';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_subscriptions';

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'pause_resumes_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'renews_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the billable model related to the subscription.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if the subscription is active, on trial, paused for free, or within its grace period.
     */
    public function valid(): bool
    {
        return $this->active() ||
            $this->onTrial() ||
            $this->pastDue() ||
            $this->cancelled() ||
            ($this->paused() && $this->pause_mode === 'free');
    }

    /**
     * Check if the subscription is on trial.
     */
    public function onTrial(): bool
    {
        return $this->status === self::STATUS_ON_TRIAL;
    }

    /**
     * Filter query by on trial.
     */
    public function scopeOnTrial(Builder $query): void
    {
        $query->where('status', self::STATUS_ON_TRIAL);
    }

    /**
     * Check if the subscription is active.
     */
    public function active(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Determine if the subscription's trial has expired.
     */
    public function hasExpiredTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    /**
     * Filter query by active.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Check if the subscription is paused.
     */
    public function paused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    /**
     * Filter query by paused.
     */
    public function scopePaused(Builder $query): void
    {
        $query->where('status', self::STATUS_PAUSED);
    }

    /**
     * Check if the subscription is past due.
     */
    public function pastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    /**
     * Filter query by past due.
     */
    public function scopePastDue(Builder $query): void
    {
        $query->where('status', self::STATUS_PAST_DUE);
    }

    /**
     * Check if the subscription is unpaid.
     */
    public function unpaid(): bool
    {
        return $this->status === self::STATUS_UNPAID;
    }

    /**
     * Filter query by unpaid.
     */
    public function scopeUnpaid(Builder $query): void
    {
        $query->where('status', self::STATUS_UNPAID);
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function cancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Filter query by cancelled.
     */
    public function scopeCancelled(Builder $query): void
    {
        $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Check if the subscription is expired.
     */
    public function expired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Filter query by expired.
     */
    public function scopeExpired(Builder $query): void
    {
        $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Determine if the subscription is within its grace period after cancellation.
     */
    public function onGracePeriod(): bool
    {
        return $this->cancelled() && $this->ends_at?->isFuture();
    }

    /**
     * Determine if the subscription is within its paused period.
     */
    public function onPausedPeriod(): bool
    {
        return $this->paused() && $this->pause_resumes_at?->isFuture();
    }

    /**
     * Determine if the subscription is on a specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return $this->product_id === $productId;
    }

    /**
     * Determine if the subscription is on a specific variant.
     */
    public function hasVariant(string $variantId): bool
    {
        return $this->variant_id === $variantId;
    }

    /**
     * Change the billing cycle anchor on the subscription.
     */
    public function anchorBillingCycleOn(?int $date): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'billing_anchor' => $date,
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * End the current trial by resetting the billing anchor to today.
     */
    public function endTrial(): self
    {
        return $this->anchorBillingCycleOn(0);
    }

    /**
     * Swap the subscription to a new product plan.
     */
    public function swap(string $product, string $variant, array $attributes = []): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => array_merge([
                    'product_id' => $product,
                    'variant_id' => $variant,
                    'disable_prorations' => ! $this->prorate,
                ], $attributes),
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Swap the subscription to a new product plan and invoice immediately.
     */
    public function swapAndInvoice(string $product, string $variant): self
    {
        return $this->swap($product, $variant, [
            'invoice_immediately' => true,
        ]);
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): self
    {
        $response = LemonSqueezy::api('DELETE', "subscriptions/{$this->lemon_squeezy_id}");

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Resume the subscription.
     */
    public function resume(): self
    {
        if ($this->expired()) {
            throw new LogicException('Cannot resume an expired subscription.');
        }

        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'cancelled' => false,
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Pause the subscription and prevent the user from using the service.
     */
    public function pause(DateTimeInterface $resumesAt = null): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'pause' => [
                        'mode' => 'void',
                        'resumes_at' => $resumesAt ? Carbon::instance($resumesAt)->toIso8601String() : null,
                    ],
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Pause the subscription but let the user continue to use the service for free.
     */
    public function pauseForFree(DateTimeInterface $resumesAt = null): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'pause' => [
                        'mode' => 'free',
                        'resumes_at' => $resumesAt ? Carbon::instance($resumesAt)->toIso8601String() : null,
                    ],
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Unpause the subscription.
     */
    public function unpause(): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'pause' => null,
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

    /**
     * Get the subscription update payment method URL.
     */
    public function updatePaymentMethodUrl(): string
    {
        $response = LemonSqueezy::api('GET', "subscriptions/{$this->lemon_squeezy_id}");

        return $response['data']['attributes']['urls']['update_payment_method'];
    }

    /**
     * Sync the subscription with the given attributes.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'status' => $attributes['status'],
            'product_id' => $attributes['product_id'],
            'variant_id' => $attributes['variant_id'],
            'card_brand' => $attributes['card_brand'] ?? null,
            'card_last_four' => $attributes['card_last_four'] ?? null,
            'pause_mode' => $attributes['pause']['mode'] ?? null,
            'pause_resumes_at' => isset($attributes['pause']['resumes_at']) ? Carbon::make($attributes['pause']['resumes_at']) : null,
            'trial_ends_at' => isset($attributes['trial_ends_at']) ? Carbon::make($attributes['trial_ends_at']) : null,
            'renews_at' => isset($attributes['renews_at']) ? Carbon::make($attributes['renews_at']) : null,
            'ends_at' => isset($attributes['ends_at']) ? Carbon::make($attributes['ends_at']) : null,
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }
}
