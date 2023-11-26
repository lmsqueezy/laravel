<?php

namespace LemonSqueezy\Laravel\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use LemonSqueezy\Laravel\LemonSqueezy;

trait ManagesOrders
{
    /**
     * Get all of the orders for the billable.
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(LemonSqueezy::$orderModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Determine if the billable has purchased a specific product.
     */
    public function hasPurchasedProduct(string $productId): bool
    {
        return $this->orders()->where('product_id', $productId)->where('status', static::STATUS_PAID)->exists();
    }

    /**
     * Determine if the billable has purchased a specific variant of a product.
     */
    public function hasPurchasedVariant(string $variantId): bool
    {
        return $this->orders()->where('variant_id', $variantId)->where('status', static::STATUS_PAID)->exists();
    }
}
