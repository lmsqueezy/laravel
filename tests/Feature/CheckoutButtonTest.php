<?php

use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use LemonSqueezy\Laravel\Checkout;

uses(InteractsWithViews::class);

it('can render a button', function () {
    $view = $this->blade(
        '<x-lemon-button :href="$href">Buy Now</x-lemon-button>',
        ['href' => 'https://lemon.lemonsqueezy.com/checkout/buy/variant_123']
    );

    $expect = <<<'HTML'
        <a
            href="https://lemon.lemonsqueezy.com/checkout/buy/variant_123"
            class="lemonsqueezy-button"
        >
            Buy Now
        </a>
        HTML;

    $view->assertSee($expect, false);
});

it('can render a checkout instance', function () {
    Http::fake([
        'api.lemonsqueezy.com/v1/checkouts' => Http::response([
            'data' => ['attributes' => ['url' => 'https://lemon.lemonsqueezy.com/checkout/buy/variant_123']],
        ]),
    ]);

    $view = $this->blade(
        '<x-lemon-button :href="$checkout">Buy Now</x-lemon-button>',
        ['checkout' => Checkout::make('store_24398', 'variant_123')]
    );

    $expect = <<<'HTML'
        <a
            href="https://lemon.lemonsqueezy.com/checkout/buy/variant_123"
            class="lemonsqueezy-button"
        >
            Buy Now
        </a>
        HTML;

    $view->assertSee($expect, false);
});
