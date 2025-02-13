<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\LicenseKeyStatus;

final class LicenseKey implements Hook
{
    public function __construct(
        public readonly int $store_id,
        public readonly int $customer_id,
        public readonly int $order_id,
        public readonly int $order_item_id,
        public readonly int $product_id,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly string $key,
        public readonly string $key_short,
        public readonly int $activation_limit,
        public readonly int $instances_count,
        public readonly int $disabled,
        public readonly LicenseKeyStatus $status,
        public readonly string $status_formatted,
        public readonly ?CarbonInterface $expires_at,
        public readonly ?CarbonInterface $created_at,
        public readonly ?CarbonInterface $updated_at,
    ) {}

    public static function fromArray(array $data): LicenseKey
    {
        return new LicenseKey(
            store_id: $data['store_id'],
            customer_id: $data['customer_id'],
            order_id: $data['order_id'],
            order_item_id: $data['order_item_id'],
            product_id: $data['product_id'],
            user_name: $data['user_name'],
            user_email: $data['user_email'],
            key: $data['key'],
            key_short: $data['key_short'],
            activation_limit: $data['activation_limit'],
            instances_count: $data['instances_count'],
            disabled: $data['disabled'],
            status: LicenseKeyStatus::from(
                value: $data['status'],
            ),
            status_formatted: $data['status_formatted'],
            expires_at: isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }
}
