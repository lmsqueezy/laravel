<?php

namespace LaravelLemonSqueezy;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LaravelLemonSqueezy\Database\Factories\SubscriptionFactory;

/**
 * @property \LaravelLemonSqueezy\Billable $billable
 */
class Subscription extends Model
{
    use HasFactory;

    const STATUS_ON_TRIAL = 'on_trial';

    const STATUS_ACTIVE = 'active';

    const STATUS_PAUSED = 'paused';

    const STATUS_PAST_DUE = 'past_due';

    const STATUS_UNPAID = 'unpaid';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_EXPIRED = 'expired';

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
        'paused_from' => 'datetime',
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

    public function swap(string $productId, string $variantId): self
    {
        $response = LemonSqueezy::api('PATCH', "subscriptions/{$this->lemon_squeezy_id}", [
            'data' => [
                'type' => 'subscriptions',
                'id' => $this->lemon_squeezy_id,
                'attributes' => [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                ],
            ],
        ]);

        $this->sync($response['data']['attributes']);

        return $this;
    }

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

    public function onTrial(): bool
    {
        return $this->status === self::STATUS_ON_TRIAL;
    }

    public function active(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function paused(): bool
    {
        return $this->status === self::STATUS_PAUSED;
    }

    public function pastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    public function unpaid(): bool
    {
        return $this->status === self::STATUS_UNPAID;
    }

    public function cancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function expired(): bool
    {
        return $this->status === self::STATUS_EXPIRED;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory<static>
     */
    protected static function newFactory()
    {
        return SubscriptionFactory::new();
    }
}
