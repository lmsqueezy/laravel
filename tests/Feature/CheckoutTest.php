<?php

use Illuminate\Http\RedirectResponse;
use LemonSqueezy\Laravel\Checkout;

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
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?logo=0&media=0&desc=0&code=0');
});

it('can set prefilled fields with dedicated methods', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withName('John Doe')
        ->withEmail('john@example.com')
        ->withBillingAddress('US', 'NY', '10038')
        ->withTaxNumber('GB123456789')
        ->withDiscountCode('10PERCENTOFF');

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?checkout%5Bname%5D=John+Doe&checkout%5Bemail%5D=john%40example.com&checkout%5Bbilling_address%5D%5Bcountry%5D=US&checkout%5Bbilling_address%5D%5Bstate%5D=NY&checkout%5Bbilling_address%5D%5Bzip%5D=10038&checkout%5Btax_number%5D=GB123456789&checkout%5Bdiscount_code%5D=10PERCENTOFF');
});

it('can include custom data', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withCustomData([
            'order_id' => '789',
        ]);

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?checkout%5Bcustom%5D%5Border_id%5D=789');
});

it('can include prefilled fields and custom data', function () {
    $checkout = Checkout::make('lemon', 'variant_123')
        ->withName('John Doe')
        ->withCustomData([
            'order_id' => '789',
        ]);

    expect($checkout->url())
        ->toBe('https://lemon.lemonsqueezy.com/checkout/buy/variant_123?checkout%5Bname%5D=John+Doe&checkout%5Bcustom%5D%5Border_id%5D=789');
});
