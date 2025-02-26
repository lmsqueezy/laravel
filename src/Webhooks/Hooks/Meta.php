<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

final class Meta implements Hook
{
    public function __construct(
        public readonly string $event_name,
    ) {}

    public static function fromArray(array $data): Meta
    {
        return new Meta(
            event_name: $data['event_name'],
        );
    }
}
