<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\CardBrand;
use LemonSqueezy\Laravel\Webhooks\Enums\SubscriptionInvoiceStatus;

final class SubscriptionInvoice implements Hook
{
    public function __construct(
        public readonly int $store_id,
        public readonly int $subscription_id,
        public readonly int $customer_id,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly string $billing_reason,
        public readonly CardBrand $card_brand,
        public readonly string $card_last_four,
        public readonly string $currency,
        public readonly string $currency_rate,
        public readonly SubscriptionInvoiceStatus $status,
        public readonly string $status_formatted,
        public readonly bool $refunded,
        public readonly CarbonInterface|null $refunded_at,
        public readonly int $subtotal,
        public readonly int $discount_total,
        public readonly int $tax,
        public readonly bool $tax_inclusive,
        public readonly int $total,
        public readonly int $refunded_amount,
        public readonly int $subtotal_usd,
        public readonly int $discount_total_usd,
        public readonly int $tax_usd,
        public readonly int $total_usd,
        public readonly int $refunded_amount_usd,
        public readonly string $subtotal_formatted,
        public readonly string $discount_total_formatted,
        public readonly string $tax_formatted,
        public readonly string $total_formatted,
        public readonly array $urls,
        public readonly CarbonInterface|null $created_at,
        public readonly CarbonInterface|null $updated_at,
        public readonly bool $test_mode,
    ) {}

    public static function fromArray(array $data): SubscriptionInvoice
    {
        return new SubscriptionInvoice(
            store_id: $data['store_id'],
            subscription_id: $data['subscription_id'],
            customer_id: $data['customer_id'],
            user_name: $data['user_name'],
            user_email: $data['user_email'],
            billing_reason: $data['billing_reason'],
            card_brand: CardBrand::from(
                value: $data['card_brand'],
            ),
            card_last_four: $data['card_last_four'],
            currency: $data['currency'],
            currency_rate: $data['currency_rate'],
            status: SubscriptionInvoiceStatus::from(
                value: $data['status'],
            ),
            status_formatted: $data['status_formatted'],
            refunded: $data['refunded'],
            refunded_at: isset($data['refunded_at']) ? Carbon::parse($data['refunded_at']) : null,
            subtotal: $data['subtotal'],
            discount_total: $data['discount_total'],
            tax: $data['tax'],
            tax_inclusive: $data['tax_inclusive'],
            total: $data['total'],
            refunded_amount: $data['refunded_amount'],
            subtotal_usd: $data['subtotal_usd'],
            discount_total_usd: $data['discount_total_usd'],
            tax_usd: $data['tax_usd'],
            total_usd: $data['total_usd'],
            refunded_amount_usd: $data['refunded_amount_usd'],
            subtotal_formatted: $data['subtotal_formatted'],
            discount_total_formatted: $data['discount_total_formatted'],
            tax_formatted: $data['tax_formatted'],
            total_formatted: $data['total_formatted'],
            urls: $data['urls'],
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            test_mode: $data['test_mode'],
        );
    }
}
