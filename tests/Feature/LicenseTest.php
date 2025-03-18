<?php

use LemonSqueezy\Laravel\LicenseKey;
use LemonSqueezy\Laravel\Exceptions\LicenseKeyNotFound;
use LemonSqueezy\Laravel\LicenseKeyInstance;
use Tests\Fixtures\User;

it('can activate a license', function () {
    $key = LicenseKey::factory()->create();
    $reference = 'some-reference';

    Http::fake([
        'api.lemonsqueezy.com/v1/licenses/activate' => Http::response([
            'license_key' => [
                'id' => 'foo',
                'key' => $key->license_key,
            ],
            'instance' => [
                'id' => 'foo',
                'name' => $reference,
            ]
        ]),
    ]);

    (new User)->activateLicense($key->license_key, $reference);

    expect(LicenseKeyInstance::where('identifier', '=', 'foo')->exists())->toBeTrue();
});

it('fails if license not found', function () {
    (new User)->activateLicense('some-key', 'some-reference');
})->throws(LicenseKeyNotFound::class);

