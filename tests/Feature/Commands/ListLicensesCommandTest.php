<?php

use Illuminate\Support\Facades\Http;
use LemonSqueezy\Laravel\LemonSqueezy;

beforeEach(function () {
    Http::fake([
        LemonSqueezy::API.'/stores/fake' => Http::response([
            'data' => [
                'id' => 'fake',
                'attributes' => [
                    'name' => 'Fake Store',
                    'currency' => 'EUR',
                ],
            ],
        ]),
        LemonSqueezy::API.'/stores/other' => Http::response([
            'data' => [
                'id' => 'other',
                'attributes' => [
                    'name' => 'Other Fake Store',
                    'currency' => 'EUR',
                ],
            ],
        ]),
        LemonSqueezy::API.'/license-keys?*' => Http::response([
            'data' => [
                [
                    'id' => '1',
                    'attributes' => [
                        'key' => '0766391F-31BA-4508-8528-887A901C6262',
                        'key_short' => 'XXXX-887A901C6262',
                        'activation_limit' => '10',
                        'activation_usage' => '2',
                        'product_id' => '12345',
                        'order_id' => 'foo',
                        'variant_id' => '67890',
                        'status' => 'active',
                        'user_name' => 'John Doe',
                        'user_email' => 'john.doe@example.com',
                    ],
                ],
                [
                    'id' => '2',
                    'attributes' => [
                        'key' => '42FF3EE3-D03B-48DC-8C2F-F447677BF33F',
                        'key_short' => 'XXXX-F447677BF33F',
                        'activation_limit' => '6',
                        'activation_usage' => '0',
                        'product_id' => '12345',
                        'order_id' => 'bar',
                        'status' => 'active',
                        'user_name' => 'Jane Doe',
                        'user_email' => 'jane.doe@example.com',
                    ],
                ],
                [
                    'id' => '3',
                    'attributes' => [
                        'key' => '9A631ABD-7F1D-4890-B211-1280B8A52A1F',
                        'key_short' => 'XXXX-1280B8A52A1F',
                        'activation_usage' => '0',
                        'product_id' => '67890',
                        'variant_id' => '12345',
                        'order_id' => 'foo',
                        'status' => 'disabled',
                        'user_name' => 'John Doe',
                        'user_email' => 'john.doe@example.com',
                    ],
                ],
            ],
        ]),
    ]);
});

it('can list licenses', function () {
    $this->artisan('lmsqueezy:licenses')

        // First License
        ->expectsOutputToContain('XXXX-887A901C6262')
        ->expectsOutputToContain('12345:67890')
        ->expectsOutputToContain('John Doe [john.doe@example.com]')

        // Second License
        ->expectsOutputToContain('XXXX-F447677BF33F')
        ->expectsOutputToContain('12345:None')
        ->expectsOutputToContain('Jane Doe [jane.doe@example.com]')

        // Third License
        ->expectsOutputToContain('XXXX-1280B8A52A1F')
        ->expectsOutputToContain('67890:12345')

        ->assertSuccessful();
});

it('can query licenses for a specific status', function () {
    $this->artisan('lmsqueezy:licenses', ['--status' => 'disabled'])
        ->expectsOutputToContain('XXXX-1280B8A52A1F')
        ->expectsOutputToContain('XXXX-887A901C6262')
        ->doesntExpectOutput('XXXX-F447677BF33F')

        ->assertSuccessful();
});

it('can query licenses for a specific product', function () {
    $this->artisan('lmsqueezy:licenses', ['product' => '12345'])
        ->doesntExpectOutput('XXXX-1280B8A52A1F')
        ->expectsOutputToContain('XXXX-887A901C6262')
        ->expectsOutputToContain('XXXX-F447677BF33F')

        ->assertSuccessful();
});

it('can query licenses for a specific order', function () {
    $this->artisan('lmsqueezy:licenses', ['--order' => 'bar'])
        ->doesntExpectOutput('XXXX-1280B8A52A1F')
        ->doesntExpectOutput('XXXX-887A901C6262')
        ->expectsOutputToContain('XXXX-F447677BF33F')

        ->assertSuccessful();
});

it('can display the full license key', function () {
    $this->artisan('lmsqueezy:licenses', ['-l' => true])
        ->expectsOutputToContain('9A631ABD-7F1D-4890-B211-1280B8A52A1F')
        ->doesntExpectOutput('XXXX-1280B8A52A1F')

        ->assertSuccessful();
});

it('can display the requested page', function () {
    $this->artisan('lmsqueezy:licenses', ['-p' => '2', '-s' => '2'])
        ->doesntExpectOutput('XXXX-887A901C6262')
        ->doesntExpectOutput('XXXX-F447677BF33F')
        ->expectsOutputToContain('XXXX-1280B8A52A1F')

        ->assertSuccessful();
});

it('fails when api key is missing', function () {
    config()->set('lemon-squeezy.api_key', null);

    $this->artisan('lmsqueezy:licenses')
        ->expectsOutputToContain('Lemon Squeezy API key not set. You can add it to your .env file as LEMON_SQUEEZY_API_KEY.');
});

it('fails when store is missing', function () {
    config()->set('lemon-squeezy.store', null);

    $this->artisan('lmsqueezy:licenses')
        ->expectsOutputToContain('Lemon Squeezy store ID not set. You can add it to your .env file as LEMON_SQUEEZY_STORE.');
});

it('returns correct products based on the store id', function () {
    config()->set('lemon-squeezy.store', 'other');

    $this->artisan('lmsqueezy:licenses')
        ->doesntExpectOutput('XXXX-887A901C6262');
});
