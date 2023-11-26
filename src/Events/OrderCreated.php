<?php

namespace LemonSqueezy\Laravel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LemonSqueezy\Laravel\Order;

class OrderCreated
{
    use Dispatchable, SerializesModels;

    /**
     * The billable entity.
     */
    public Model $billable;

    /**
     * The order entity.
     *
     * @todo v2: Remove the nullable type hint.
     */
    public ?Order $order;

    /**
     * The payload array.
     */
    public array $payload;

    public function __construct(Model $billable, ?Order $order, array $payload)
    {
        $this->billable = $billable;
        $this->order = $order;
        $this->payload = $payload;
    }
}
