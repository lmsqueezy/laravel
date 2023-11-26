<?php

use LemonSqueezy\Laravel\Order;

it('can display formatted amounts', function () {
    $order = new Order([
        'subtotal' => 1000,
        'discount_total' => 10,
        'tax' => 100,
        'total' => 1090,
        'currency' => 'USD',
    ]);

    expect($order->subtotal())->toBe('$10.00');
    expect($order->discount())->toBe('$0.10');
    expect($order->tax())->toBe('$1.00');
    expect($order->total())->toBe('$10.90');
});
