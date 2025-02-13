<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Hooks;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Webhooks\Enums\PauseMode;

final class Pause
{
    public function __construct(
        public readonly PauseMode $mode,
        public readonly ?CarbonInterface $resumes_at,
    ) {}

    public static function fromArray(array $data): Pause
    {
        return new Pause(
            mode: PauseMode::from(
                value: $data['mode'],
            ),
            resumes_at: isset($data['resumes_at']) ? Carbon::parse($data['resumes_at']) : null,
        );
    }
}
