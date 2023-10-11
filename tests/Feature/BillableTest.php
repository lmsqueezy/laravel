<?php

use LemonSqueezy\Laravel\Customer;
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

it('can not overwrite the customer id and type or subscription id for a billable', function () {
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

it('can generate a customer portal link for a billable', function () {
    config()->set('lemon-squeezy.store', 'store_23432');

    Http::fake([
        'api.lemonsqueezy.com/v1/customers/1' => Http::response([
            'data' => ['attributes' => ['urls' => [
                'customer_portal' => 'https://my-store.lemonsqueezy.com/billing?expires=1666869343&signature=xxxxx',
            ]]],
        ]),
    ]);

    $user = new User;
    $user->customer = (object) ['lemon_squeezy_id' => 1];

    $url = $user->customerPortalUrl();

    expect($url)
        ->toBe('https://my-store.lemonsqueezy.com/billing?expires=1666869343&signature=xxxxx');
});

it('can determine the generic trial on a billable', function () {
    $user = User::factory()->create();

    $customer = $user->createAsCustomer();

    expect($customer)->toBeInstanceOf(Customer::class);
});
