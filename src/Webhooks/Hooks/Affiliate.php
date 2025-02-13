<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\AffiliateStatus;

final class Affiliate implements Hook
{
    public function __construct(
        public readonly int $store_id,
        public readonly int $user_id,
        public readonly string $user_name,
        public readonly string $user_email,
        public readonly string $share_domain,
        public readonly AffiliateStatus $status,
        public readonly ?array $products,
        public readonly string $application_note,
        public readonly int $total_earnings,
        public readonly int $unpaid_earnings,
        public readonly ?CarbonInterface $created_at,
        public readonly ?CarbonInterface $updated_at,
    ) {}

    public static function fromArray(array $data): Affiliate
    {
        return new Affiliate(
            store_id: $data['store_id'],
            user_id: $data['user_id'],
            user_name: $data['user_name'],
            user_email: $data['user_email'],
            share_domain: $data['share_domain'],
            status: AffiliateStatus::from(
                value: $data['status'],
            ),
            products: $data['products'],
            application_note: $data['application_note'],
            total_earnings: $data['total_earnings'],
            unpaid_earnings: $data['unpaid_earnings'],
            created_at: isset($data['created_at']) ? Carbon::parse($data['created_at']) : null,
            updated_at: isset($data['updated_at']) ? Carbon::parse($data['updated_at']) : null,
        );
    }
}
