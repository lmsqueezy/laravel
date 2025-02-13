<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class SubscriptionItem implements Hook
{
    public function __construct(
        public readonly int $id,
        public readonly int $subscription_id,
        public readonly int $quantity,
        public CarbonInterface|null $created_at,
        public CarbonInterface|null $updated_at,
    ) {}

    public static function fromArray(array $data): SubscriptionItem
    {
        return new SubscriptionItem(
            id: $data['id'],
            subscription_id: $data['subscription_id'],
            quantity: $data['quantity'],
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }
}
