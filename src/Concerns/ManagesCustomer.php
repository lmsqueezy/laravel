<?php

namespace LemonSqueezy\Laravel\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;
use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomer;
use LemonSqueezy\Laravel\LemonSqueezy;

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
     * Get the billable's name to associate with Lemon Squeezy.
     */
    public function lemonSqueezyName(): ?string
    {
        return $this->name ?? null;
    }

    /**
     * Get the billable's email address to associate with Lemon Squeezy.
     */
    public function lemonSqueezyEmail(): ?string
    {
        return $this->email ?? null;
    }

    /**
     * Get the billable's country to associate with Lemon Squeezy.
     *
     * This needs to be a 2 letter code.
     */
    public function lemonSqueezyCountry(): ?string
    {
        return $this->country ?? null; // 'US'
    }

    /**
     * Get the billable's zip code to associate with Lemon Squeezy.
     */
    public function lemonSqueezyZip(): ?string
    {
        return $this->zip ?? null; // '10038'
    }

    /**
     * Get the billable's tax number to associate with Lemon Squeezy.
     */
    public function lemonSqueezyTaxNumber(): ?string
    {
        return $this->tax_number ?? null; // 'GB123456789'
    }

    /**
     * Get the customer portal url for this billable.
     */
    public function customerPortalUrl(): string
    {
        $this->assertCustomerExists();

        $response = LemonSqueezy::api('GET', "customers/{$this->customer->lemon_squeezy_id}");

        return $response['data']['attributes']['urls']['customer_portal'];
    }

    /**
     * Generate a redirect response to the billable's customer portal.
     */
    public function redirectToCustomerPortal(): RedirectResponse
    {
        return new RedirectResponse($this->customerPortalUrl());
    }

    /**
     * Determine if the billable is already a Lemon Squeezy customer and throw an exception if not.
     *
     * @throws InvalidCustomer
     */
    protected function assertCustomerExists(): void
    {
        if (is_null($this->customer) || is_null($this->customer->lemon_squeezy_id)) {
            throw InvalidCustomer::notYetCreated($this);
        }
    }
}
