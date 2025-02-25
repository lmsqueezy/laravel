<?php

declare(strict_types=1);

namespace LemonSqueezy\Laravel\Webhooks\Handlers;

use LemonSqueezy\Laravel\Events\LicenseKeyUpdated;
use LemonSqueezy\Laravel\Exceptions\InvalidCustomPayload;
use LemonSqueezy\Laravel\Webhooks\Concerns\CanResolveBillable;

final class HandleLicenseKeyUpdated
{
    use CanResolveBillable;

    /**
     * Handle the license key updated event.
     *
     * @param array $payload
     * @return void
     * @throws InvalidCustomPayload
     */
    public function handle(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        LicenseKeyUpdated::dispatch($billable, $payload);
    }
}
