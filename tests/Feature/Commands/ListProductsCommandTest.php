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
        LemonSqueezy::API.'/products/fake?*' => Http::response([
            'data' => [
                'id' => 'fake',
                'attributes' => [
                    'name' => 'Fake Product',
                ],
                'relationships' => [
                    'variants' => [
                        'data' => [
                            ['id' => '123'],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => '123',
                    'type' => 'variants',
                    'attributes' => [
                        'name' => 'Fake Variant',
                        'price' => 999,
                    ],
                ],
            ],
        ]),
        LemonSqueezy::API.'/products?*' => Http::response([
            'data' => [
                [
                    'id' => '1',
                    'attributes' => [
                        'name' => 'Pro',
                    ],
                    'relationships' => [
                        'variants' => [
                            'data' => [
                                ['id' => '123'],
                            ],
                        ],
                    ],
                ],
                [
                    'id' => '2',
                    'attributes' => [
                        'name' => 'Test',
                    ],
                    'relationships' => [
                        'variants' => [
                            'data' => [
                                ['id' => '321'],
                                ['id' => '456'],
                            ],
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => '123',
                    'type' => 'variants',
                    'attributes' => [
                        'name' => 'Default',
                        'price' => 999,
                    ],
                ],
                [
                    'id' => '321',
                    'type' => 'variants',
                    'attributes' => [
                        'name' => 'Monthly',
                        'price' => 929,
                    ],
                ],
                [
                    'id' => '456',
                    'type' => 'variants',
                    'attributes' => [
                        'name' => 'Yearly',
                        'price' => 939,
                    ],
                ],
            ],
        ]),
    ]);
});

it('can list products', function () {
    $this->artisan('lmsqueezy:products')

        // First Product
        ->expectsOutputToContain('Pro')
        ->expectsOutputToContain('Default €9.99')

        // Second Product
        ->expectsOutputToContain('Test')
        ->expectsOutputToContain('Monthly €9.29')
        ->expectsOutputToContain('Yearly €9.39')

        ->assertSuccessful();
});

it('can query a specific product', function () {
    $this->artisan('lmsqueezy:products', ['product' => 'fake'])
        ->expectsOutputToContain('Fake Product')
        ->expectsOutputToContain('Fake Variant €9.99')
        ->assertSuccessful();
});

it('fails when api key is missing', function () {
    config()->set('lemon-squeezy.api_key', null);

    $this->artisan('lmsqueezy:products')
        ->expectsOutputToContain('Lemon Squeezy API key not set. You can add it to your .env file as LEMON_SQUEEZY_API_KEY.');
});

it('fails when store is missing', function () {
    config()->set('lemon-squeezy.store', null);

    $this->artisan('lmsqueezy:products')
        ->expectsOutputToContain('Lemon Squeezy store ID not set. You can add it to your .env file as LEMON_SQUEEZY_STORE.');
});
