<?php

namespace LemonSqueezy\Laravel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LemonSqueezy\Laravel\Database\Factories\DiscountFactory;

/**
 * @property \LemonSqueezy\Laravel\Billable $billable
 */
class Discount extends Model
{
    use HasFactory;

    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_discounts';

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
        'amount' => 'integer',
        'is_limited_to_products' => 'boolean',
        'is_limited_redemptions' => 'boolean',
        'max_redemptions' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'duration_in_months' => 'integer',
    ];

     /**
     * Get the billable model related to the customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if the discount code is valid.
     */ 
    public static function isValidCode(string $code): bool
    {
        return preg_match('/^[A-Z0-9]{3,256}$/', $code);
    }

    /**
     * Calculate the discount amount.
     */
    public function calculateDiscountAmount(int $amount): int
    {
        if ($this->amount_type === 'percent') {
            return (int) round($amount * ($this->amount / 100));
        }

        return $this->amount;
    }
     
    /**
     * Determine if the discount is active.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->is_limited_redemptions && $this->hasReachedMaxRedemptions()) {
            return false;
        }

        if ($this->duration === 'repeating' && $this->starts_at) {
            $monthsSinceStart = now()->diffInMonths($this->starts_at);
            $monthsIntoCurrentPeriod = $monthsSinceStart % $this->duration_in_months;
            if ($monthsIntoCurrentPeriod >= $this->duration_in_months) {
                return false;
            }
        }

        return true;
    }

    /**
     * Increment the redemptions count.
     */
    public function incrementRedemptions($orderId): void
    {
        if (!$this->hasReachedMaxRedemptions()) {
            DiscountRedemption::create([
                'discount_id' => $this->id,
                'order_id' => $orderId,
                'billable_id' => $this->billable_id,
                'billable_type' => $this->billable_type,
                'lemon_squeezy_id' => $this->lemon_squeezy_id,
                'discount_name' => $this->name,
                'discount_code' => $this->code,
                'discount_amount' => $this->amount,
                'discount_amount_type' => $this->amount_type,
                'amount' => $this->amount,
            ]);
        }
    }

    /**
     * Has reached the maximum redemptions.
     */
    public function hasReachedMaxRedemptions(): bool
    {
        return $this->is_limited_redemptions && $this->max_redemptions && $this->max_redemptions <= $this->redemptions()->count();
    }

    /**
     * Check if the discount is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Filter query by draft.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Filter query by published.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Filter query by active.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                     ->where(function ($query) {
                         $query->whereNull('expires_at')
                               ->orWhere('expires_at', '>', now());
                     });
    }

    /**
     * Filter query by expired.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED)
                     ->where('expires_at', '<=', now());
    }

    /**
     * Get the redemptions for the discount.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(DiscountRedemption::class);
    }

    /**
     * Sync the order with the given attributes.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'name' => $attributes['name'],
            'code' => $attributes['code'],
            'amount' => $attributes['amount'],
            'amount_type' => $attributes['amount_type'],
            'is_limited_to_products' => $attributes['is_limited_to_products'],
            'is_limited_redemptions' => $attributes['is_limited_redemptions'],
            'max_redemptions' => $attributes['max_redemptions'],
            'starts_at' => isset($attributes['starts_at']) ? Carbon::make($attributes['starts_at']) : null,
            'expires_at' => isset($attributes['expires_at']) ? Carbon::make($attributes['expires_at']) : null,
            'duration' => $attributes['duration'],
            'duration_in_months' => $attributes['duration_in_months'],
            'status' => $attributes['status'],
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DiscountFactory
    {
        return DiscountFactory::new();
    }
}
