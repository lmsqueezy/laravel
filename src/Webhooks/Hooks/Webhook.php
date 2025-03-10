<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

final class Webhook
{
    public function __construct(
        public readonly Meta $meta,
        public readonly string $type,
        public readonly string $id,
        public readonly Hook $attributes,
    ) {}

    public static function fromArray(array $data): Webhook
    {
        // create Webhook payload
//        $hook = match ($data['type']) {
//
//        };

        return new Webhook(
            meta: Meta::fromArray($data['meta']),
            type: $data['type'],
            id: $data['id'],
            attributes: $hook,
        );
    }
}
