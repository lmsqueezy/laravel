<?php

namespace LaravelLemonSqueezy\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use LaravelLemonSqueezy\Customer;

trait ManagesCustomer
{
    /**
     * Create a customer record for the billable model.
     */
    public function createAsCustomer(array $attributes = []): Customer
    {
        return $this->customer()->create($attributes);
    }

    /**
     * Get the customer related to the billable model.
     */
    public function customer(): MorphOne
    {
        return $this->morphOne(Customer::class, 'billable');
    }

    /**
     * Get the billable model's name to associate with Lemon Squeezy.
     */
    public function lemonSqueezyName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the billable model's email address to associate with Lemon Squeezy.
     */
    public function lemonSqueezyEmail(): ?string
    {
        return $this->email ?? null;
    }

    /**
     * Get the billable model's country to associate with Lemon Squeezy.
     *
     * This needs to be a 2 letter code.
     */
    public function lemonSqueezyCountry(): ?string
    {
        return $this->country ?? null; // 'US'
    }

    /**
     * Get the billable model's state to associate with Lemon Squeezy.
     */
    public function lemonSqueezyState(): ?string
    {
        return $this->state ?? null; // 'NY'
    }

    /**
     * Get the billable model's zip code to associate with Lemon Squeezy.
     */
    public function lemonSqueezyZip(): ?string
    {
        return $this->zip ?? null; // '10038'
    }

    /**
     * Get the billable model's tax number to associate with Lemon Squeezy.
     */
    public function lemonSqueezyTaxNumber(): ?string
    {
        return $this->tax_number ?? null; // 'GB123456789'
    }
}
