<?php

namespace LemonSqueezy\Laravel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use LemonSqueezy\Laravel\Database\Factories\DiscountRedemptionFactory;

class DiscountRedemption extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lemon_squeezy_discount_redemptions';

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

     /**
     * Get the billable model related to the customer.
     */
    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the discount associated with the redemption.
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the order associated with the redemption.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

     /**
     * Sync the order with the given attributes.
     */
    public function sync(array $attributes): self
    {
        $this->update([
            'discount_id' => $attributes['discount_id'],
            'order_id' => $attributes['order_id'],
            'discount_name' => $attributes['discount_name'],
            'discount_code' => $attributes['discount_code'],
            'discount_amount' => $attributes['discount_amount'],
            'discount_amount_type' => $attributes['discount_amount_type'],
            'amount' => $attributes['amount'],
        ]);
    
        return $this;
    }

     /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DiscountRedemptionFactory
    {
        return DiscountRedemptionFactory::new();
    }
}

