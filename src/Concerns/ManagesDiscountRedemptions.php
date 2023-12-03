<?php

namespace LemonSqueezy\Laravel\Concerns;

use LemonSqueezy\Laravel\LemonSqueezy;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ManagesDiscountRedemptions
{
    /**
     * Get all of the discounts for the billable.
     */
    public function discountRedemptions(): MorphMany
    {
        return $this->morphMany(LemonSqueezy::$discountRedemptionModel, 'billable')->orderByDesc('created_at');
    }

    public function hasAppliedDiscount(string $discountId): bool
    {
        return $this->discountRedemptions()->where('discount_id', $discountId)->exists();
    }

    public function hasAppliedDiscountToOrder(string $orderId): bool
    {
        return $this->discountRedemptions()->where('order_id', $orderId)->exists();
    }
}