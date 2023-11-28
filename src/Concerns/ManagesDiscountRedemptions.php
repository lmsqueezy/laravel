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
}