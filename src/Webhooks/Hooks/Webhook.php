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
}
