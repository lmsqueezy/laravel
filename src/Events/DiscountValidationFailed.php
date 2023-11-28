<?php

namespace LemonSqueezy\Laravel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LemonSqueezy\Laravel\Discount;

class DiscountValidationFailed
{
    use Dispatchable, SerializesModels;

    /**
     * The billable entity.
     */
    public Model $billable;

    /**
     * The discount instance.
     */
    public Discount $discount;

    /**
     * The payload array.
     */
    public array $payload;

    public function __construct(Model $billable, Discount $discount, array $payload)
    {
        $this->billable = $billable;
        $this->discount = $discount;
        $this->payload = $payload;
    }
}
