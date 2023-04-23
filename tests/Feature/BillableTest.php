<?php

use Tests\Fixtures\User;

it('can generate a checkout for a billable', function () {
    config()->set('lemon-squeezy.store', 'store_23432');

    Http::fake([
        'api.lemonsqueezy.com/v1/checkouts' => Http::response([
            'data' => ['attributes' => ['url' => 'https://lemon.lemonsqueezy.com/checkout/buy/variant_123']],
        ]),
    ]);

    $checkout = (new User)->checkout('variant_123');

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123');
});

it('can generate a checkout for a billable with custom data', function () {
    config()->set('lemon-squeezy.store', 'store_23432');

    Http::fake([
        'api.lemonsqueezy.com/v1/checkouts' => Http::response([
            'data' => ['attributes' => ['url' => 'https://lemon.lemonsqueezy.com/checkout/buy/variant_123']],
        ]),
    ]);

    $checkout = (new User)->checkout('variant_123')
        ->withCustomData(['batch_id' => '789']);

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123');
});

it('cannnot overwrite the customer id and type or subscription id for a billable', function () {
    config()->set('lemon-squeezy.store', 'store_23432');

    $this->expectExceptionMessage(
        'You cannot use "billable_id", "billable_type" or "subscription_type" as custom data keys because these are reserved keywords.'
    );

    (new User)->checkout('variant_123')
        ->withCustomData([
            'billable_id' => '567',
            'billable_type' => 'App\\Models\\User',
        ]);
});

it('needs a configured store to generate checkouts', function () {
    config()->set('lemon-squeezy.store', null);

    $this->expectExceptionMessage('The Lemon Squeezy store was not configured.');

    (new User)->checkout('variant_123');
});
