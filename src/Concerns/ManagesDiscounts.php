<?php

namespace LemonSqueezy\Laravel\Concerns;

use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Exceptions\InvalidDiscount;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ManagesDiscounts
{
    /**
     * Get all of the discounts for the billable.
     */
    public function discounts(): MorphMany
    {
        return $this->morphMany(LemonSqueezy::$discountModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Find a discount by its code.
     */
    public function findDiscountByCode(string $code): ?Discount
    {
        if (!Discount::isValidCode($code)) {
            throw new InvalidDiscount("Invalid discount code format.");
        }

        return $this->discounts()->where('code', $code)->first();
    }

    /**
     * Determine if a discount code is applicable.
     */
    public function isDiscountApplicable(string $code): bool
    {
        $discount = $this->findDiscountByCode($code);

        return $discount && $discount->isActive();
    }

    /**
     * Apply a discount to the billable.
     */
    public function applyDiscount(string $code, $orderId): ?Discount
    {
        $discount = $this->getValidDiscount($code);

        $discount->incrementRedemptions($orderId);
        $discount->billable()->associate($this);

        $discount->save();

        return $discount;
    }


    /**
     * Calculate the discount amount for a given price.
     */
    public function calculateDiscountAmount(string $code, int $price): int
    {
        $discount = $this->getValidDiscount($code);

        return $discount->calculateDiscountAmount($price);
    }

    /**
     * Get a valid discount or throw an exception.
     */
    protected function getValidDiscount(string $code): Discount
    {
        $discount = $this->findDiscountByCode($code);

        if (!$discount || !$discount->isActive() || $discount->isExpired()) {
            throw new InvalidDiscount("Discount code '{$code}' is not applicable or has expired.");
        }

        return $discount;
    }
}