<?php

namespace LemonSqueezy\Laravel\Concerns;

use LemonSqueezy\Laravel\Exceptions\LemonSqueezyApiError;
use LemonSqueezy\Laravel\Exceptions\LicenseKeyNotFound;
use LemonSqueezy\Laravel\Exceptions\LicenseKeyNotValidated;
use LemonSqueezy\Laravel\Exceptions\MalformedDataError;
use LemonSqueezy\Laravel\LemonSqueezy;
use LemonSqueezy\Laravel\LicenseKey;
use LemonSqueezy\Laravel\LicenseKeyInstance;

trait ManagesLicenses
{
    /**
     * Activate a license key.
     *
     * @param string $key The license key
     * @param string $reference External reference to store in Lemonsqueezy
     *
     * @throws MalformedDataError
     * @throws LemonSqueezyApiError
     * @throws LicenseKeyNotFound
     */
    public function activateLicense(string $key, string $reference): LicenseKeyInstance
    {
        if (!LicenseKey::notDisabled()->withKey($key)->exists()) {
            throw LicenseKeyNotFound::withKey($key);
        }

        $res = LemonSqueezy::api('POST', 'licenses/activate', [
            'license_key' => $key,
            'instance_name' => $reference,
        ]);

        return LicenseKeyInstance::fromPayload($res->json());
    }

    /**
     * @throws MalformedDataError
     * @throws LicenseKeyNotValidated
     */
    public function assertValid(string $licenseKey, ?string $instanceId = null): LicenseKey {
        try {
            $res = LemonSqueezy::api('POST', 'licenses/validate', [
                'license_key' => $licenseKey,
                'instance_id' => $instanceId,
            ]);
        } catch (LemonSqueezyApiError $e) {
            throw LicenseKeyNotValidated::withErrorMessage($e->getMessage());
        }

        return LicenseKey::fromPayload($res['data']);
    }
}
