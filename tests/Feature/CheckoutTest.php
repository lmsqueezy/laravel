<?php

use Illuminate\Http\RedirectResponse;
use LaravelLemonSqueezy\Checkout;

it('can initiate a new checkout', function () {
    $checkout = new Checkout('lemon', 'variant_123');

    expect($checkout)->toBeInstanceOf(Checkout::class);
    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123');
});

it('can be redirected', function () {
    $checkout = new Checkout('lemon', 'variant_123');

    expect($checkout->redirect())->toBeInstanceOf(RedirectResponse::class);
    expect($checkout->redirect()->getTargetUrl())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123');
});

it('can turn off toggles', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withoutLogo()
        ->withoutMedia()
        ->withoutDescription()
        ->withoutCode();

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?logo=0&media=0&description=0&code=0');
});

it('can set prefilled fields with dedicated methods', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withName('John Doe')
        ->withEmail('john@example.com')
        ->withBillingAddress('US', 'NY', '10038')
        ->withTaxNumber('GB123456789')
        ->withDiscountCode('10PERCENTOFF');

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?name=John+Doe&email=john%40example.com&billing_address%5Bcountry%5D=US&billing_address%5Bstate%5D=NY&billing_address%5Bzip%5D=10038&tax_number=GB123456789&discount_code=10PERCENTOFF');
});

it('can include custom data', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withCustomData([
            'order_id' => '789',
        ]);

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?checkout%5Bcustom%5D%5Border_id%5D=789');
});
