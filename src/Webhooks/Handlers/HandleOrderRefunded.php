<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use Illuminate\Support\Facades\Schema;
use LemonSqueezy\Laravel\Events\OrderRefunded;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleOrderRefunded
{
    use CanResolveBillable;

    /**
     * Handle the order refunded event.
     *
     * @param array $payload
     * @return void
     * @throws InvalidCustomPayload
     */
    public function handle(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        // Todo v2: Remove this check
        if (Schema::hasTable((new LemonSqueezy::$orderModel())->getTable())) {
            if (!$order = $this->findOrder($payload['data']['id'])) {
                return;
            }

            $order = $order->sync($payload['data']['attributes']);
        } else {
            $order = null;
        }

        OrderRefunded::dispatch($billable, $order, $payload);
    }
}
