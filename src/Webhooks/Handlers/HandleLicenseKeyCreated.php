<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\LicenseKeyCreated;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleLicenseKeyCreated
{
    use CanResolveBillable;

    /**
     * Handle the license key created event.
     *
     *
     * @param array $payload
     * @return void
     * @throws InvalidCustomPayload
     */
    public function handle(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        LicenseKeyCreated::dispatch($billable, $payload);
    }
}
