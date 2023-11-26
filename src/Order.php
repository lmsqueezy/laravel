<?php

namespace LemonSqueezy\Laravel;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use LemonSqueezy\Laravel\Database\Factories\OrderFactory;

/**
 * @property \LemonSqueezy\Laravel\Billable $billable
 */
class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_FAILED = 'failed';

    const STATUS_PAID = 'paid';

    const STATUS_REFUNDED = 'refunded';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_orders';

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
        'subtotal' => 'integer',
        'discount_total' => 'integer',
        'tax' => 'integer',
        'total' => 'integer',
        'refunded' => 'boolean',
        'refunded_at' => 'datetime',
        'ordered_at' => 'datetime',
    ];

    /**
     * Get the billable model related to the customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the order is pending.
     */
    public function pending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Filter query by pending.
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Check if the order is failed.
     */
    public function failed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Filter query by failed.
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Check if the order is paid.
     */
    public function paid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Filter query by paid.
     */
    public function scopePaid(Builder $query): void
    {
        $query->where('status', self::STATUS_PAID);
    }

    /**
     * Check if the order is refunded.
     */
    public function refunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Filter query by refunded.
     */
    public function scopeRefunded(Builder $query): void
    {
        $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Determine if the order is for a specific product.
     */
    public function hasProduct(string $productId): bool
    {
        return $this->product_id === $productId;
    }

    /**
     * Determine if the order is for a specific variant.
     */
    public function hasVariant(string $variantId): bool
    {
        return $this->variant_id === $variantId;
    }

    /**
     * Get the order's subtotal.
     */
    public function subtotal(): string
    {
        return LemonSqueezy::formatAmount($this->subtotal, $this->currency);
    }

    /**
     * Get the order's discount total.
     */
    public function discount(): string
    {
        return LemonSqueezy::formatAmount($this->discount_total, $this->currency);
    }

    /**
     * Get the order's tax.
     */
    public function tax(): string
    {
        return LemonSqueezy::formatAmount($this->tax, $this->currency);
    }

    /**
     * Get the order's total.
     */
    public function total(): string
    {
        return LemonSqueezy::formatAmount($this->total, $this->currency);
    }

    /**
     * Sync the order with the given attributes.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'customer_id' => $attributes['customer_id'],
            'product_id' => $attributes['product_id'],
            'variant_id' => $attributes['variant_id'],
            'order_number' => $attributes['order_number'],
            'currency' => $attributes['currency'],
            'subtotal' => $attributes['subtotal'],
            'discount_total' => $attributes['discount_total'],
            'tax' => $attributes['tax'],
            'total' => $attributes['total'],
            'tax_name' => $attributes['tax_name'],
            'status' => $attributes['status'],
            'receipt_url' => $attributes['urls']['receipt'] ?? null,
            'refunded' => $attributes['refunded'],
            'refunded_at' => isset($attributes['refunded_at']) ? Carbon::make($attributes['refunded_at']) : null,
            'ordered_at' => isset($attributes['created_at']) ? Carbon::make($attributes['created_at']) : null,
        ]);

        return $this;
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
