<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use LemonSqueezy\Laravel\Events\OrderCreated;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleOrderCreated
{
    use CanResolveBillable;

    /**
     * Handle the order created event.
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
            $attributes = $payload['data']['attributes'];

            $order = $billable->orders()->create([
                'lemon_squeezy_id' => $payload['data']['id'],
                'customer_id' => $attributes['customer_id'],
                'product_id' => (string) $attributes['first_order_item']['product_id'],
                'variant_id' => (string) $attributes['first_order_item']['variant_id'],
                'identifier' => $attributes['identifier'],
                'order_number' => $attributes['order_number'],
                'currency' => $attributes['currency'],
                'subtotal' => $attributes['subtotal'],
                'discount_total' => $attributes['discount_total'],
                'tax' => $attributes['tax'],
                'total' => $attributes['total'],
                'tax_name' => $attributes['tax_name'],
                'status' => $attributes['status'],
                'receipt_url' => $attributes['urls']['receipt'] ?? null,
                'refunded' => $attributes['refunded'],
                'refunded_at' => $attributes['refunded_at'] ? Carbon::make($attributes['refunded_at']) : null,
                'ordered_at' => Carbon::make($attributes['created_at']),
            ]);
        } else {
            $order = null;
        }

        OrderCreated::dispatch($billable, $order, $payload);
    }
}
