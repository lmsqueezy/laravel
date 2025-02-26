<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class OrderItem implements Hook
{
    public function __construct(
        public readonly int $id,
        public readonly int $order_id,
        public readonly int $product_id,
        public readonly int $variant_id,
        public readonly string $product_name,
        public readonly string $variant_name,
        public readonly int $price,
        public readonly ?CarbonInterface $created_at,
        public readonly ?CarbonInterface $updated_at,
        public readonly bool $test_mode,
    ) {}

    public static function fromArray(array $data): OrderItem
    {
        return new OrderItem(
            id: $data['id'],
            order_id: $data['order_id'],
            product_id: $data['product_id'],
            variant_id: $data['variant_id'],
            product_name: $data['product_name'],
            variant_name: $data['variant_name'],
            price: $data['price'],
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
            test_mode: $data['test_mode'],
        );
    }
}
