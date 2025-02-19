<?php

namespace LemonSqueezy\Laravel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LemonSqueezy\Laravel\LicenseKey;

class LicenseKeyUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Model $billable, public readonly LicenseKey $licenseKey)
    {
    }
}
