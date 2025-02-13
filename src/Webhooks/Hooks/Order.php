<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\OrderStatus;

final class Order implements Hook
{
    public function __construct(
        public readonly int $store_id,
        public readonly int $customer_id,
        public readonly string $identifier,
        public readonly int $order_number,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly string $currency,
        public readonly string $currency_rate,
        public readonly int $subtotal,
        public readonly int $setup_fee,
        public readonly int $discount_total,
        public readonly int $tax,
        public readonly int $total,
        public readonly int $subtotal_usd,
        public readonly int $setup_fee_usd,
        public readonly int $discount_total_usd,
        public readonly int $tax_usd,
        public readonly int $total_usd,
        public readonly string $tax_name,
        public readonly string $tax_rate,
        public readonly bool $tax_inclusive,
        public readonly OrderStatus $status,
        public readonly string $status_formatted,
        public readonly bool $refunded,
        public readonly CarbonInterface|null $refunded_at,
        public readonly string $subtotal_formatted,
        public readonly string $setup_fee_formatted,
        public readonly string $discount_total_formatted,
        public readonly string $tax_formatted,
        public readonly string $total_formatted,
        public readonly OrderItem $first_order_item,
        public readonly array $urls,
        public readonly CarbonInterface|null $created_at,
        public readonly CarbonInterface|null $updated_at,
        public readonly bool $test_mode,
    ) {}

    public static function fromArray(array $data): Order
    {
        return new Order(
            store_id: $data['store_id'],
            customer_id: $data['customer_id'],
            identifier: $data['identifier'],
            order_number: $data['order_number'],
            user_name: $data['user_name'],
            user_email: $data['user_email'],
            currency: $data['currency'],
            currency_rate: $data['currency_rate'],
            subtotal: $data['subtotal'],
            setup_fee: $data['setup_fee'],
            discount_total: $data['discount_total'],
            tax: $data['tax'],
            total: $data['total'],
            subtotal_usd: $data['subtotal_usd'],
            setup_fee_usd: $data['setup_fee_usd'],
            discount_total_usd: $data['discount_total_usd'],
            tax_usd: $data['tax_usd'],
            total_usd: $data['total_usd'],
            tax_name: $data['tax_name'],
            tax_rate: $data['tax_rate'],
            tax_inclusive: $data['tax_inclusive'],
            status: OrderStatus::from(
                value: $data['status'],
            ),
            status_formatted: $data['status_formatted'],
            refunded: $data['refunded'],
            refunded_at: isset($data['refunded_at']) ? Carbon::parse($data['refunded_at']) : null,
            subtotal_formatted: $data['subtotal_formatted'],
            setup_fee_formatted: $data['setup_fee_formatted'],
            discount_total_formatted: $data['discount_total_formatted'],
            tax_formatted: $data['tax_formatted'],
            total_formatted: $data['total_formatted'],
            first_order_item: OrderItem::fromArray($data['first_order_item']),
            urls: $data['urls'],
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            test_mode: $data['test_mode'],
        );
    }
}
