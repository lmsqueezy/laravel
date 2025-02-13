<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\CardBrand;
use LemonSqueezy\Laravel\Webhooks\Enums\SubscriptionStatus;

final class Subscription implements Hook
{
    public function __construct(
        public readonly int $store_id,
        public readonly int $customer_id,
        public readonly int $order_id,
        public readonly int $order_item_id,
        public readonly int $product_id,
        public readonly int $variant_id,
        public readonly string $product_name,
        public readonly string $variant_name,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly SubscriptionStatus $status,
        public readonly string $status_formatted,
        public readonly CardBrand $card_brand,
        public readonly string $card_last_four,
        public readonly ?Pause $pause,
        public readonly bool $cancelled,
        public readonly ?CarbonInterface $trial_ends_at,
        public readonly int $billing_anchor,
        public readonly SubscriptionItem $first_subscription_item,
        public readonly array $urls,
        public readonly ?CarbonInterface $renews_at,
        public readonly ?CarbonInterface $ends_at,
        public readonly ?CarbonInterface $created_at,
        public readonly ?CarbonInterface $updated_at,
        public readonly bool $test_mode,
    ) {}

    public static function fromArray(array $data): Subscription
    {
        return new Subscription(
            store_id: $data['store_id'],
            customer_id: $data['customer_id'],
            order_id: $data['order_id'],
            order_item_id: $data['order_item_id'],
            product_id: $data['product_id'],
            variant_id: $data['variant_id'],
            product_name: $data['product_name'],
            variant_name: $data['variant_name'],
            user_name: $data['user_name'],
            user_email: $data['user_email'],
            status: SubscriptionStatus::from(
                value: $data['status'],
            ),
            status_formatted: $data['status_formatted'],
            card_brand: CardBrand::from(
                value: $data['card_brand'],
            ),
            card_last_four: $data['card_last_four'],
            pause: isset($data['pause']) ? Pause::fromArray($data['pause']) : null,
            cancelled: $data['cancelled'],
            trial_ends_at: isset($data['trial_ends_at']) ? Carbon::parse($data['trial_ends_at']) : null,
            billing_anchor: $data['billing_anchor'],
            first_subscription_item: SubscriptionItem::fromArray($data['first_subscription_item']),
            urls: $data['urls'],
            renews_at: isset($data['renews_at']) ? Carbon::parse($data['renews_at']) : null,
            ends_at: isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            test_mode: $data['test_mode'],
        );
    }
}
