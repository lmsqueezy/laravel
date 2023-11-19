<?php

use LemonSqueezy\Laravel\Order as LemonSqueezyOrder;

it('can determine if the order is pending', function () {
    $order = new Order(['status' => Order::STATUS_PENDING]);

    expect($order->pending())->toBeTrue();
    expect($order->paid())->toBeFalse();
});

it('can determine if the order is failed', function () {
    $order = new Order(['status' => Order::STATUS_FAILED]);

    expect($order->failed())->toBeTrue();
    expect($order->paid())->toBeFalse();
});

it('can determine if the order is paid', function () {
    $order = new Order(['status' => Order::STATUS_PAID]);

    expect($order->paid())->toBeTrue();
    expect($order->failed())->toBeFalse();
});

it('can determine if the order is refunded', function () {
    $order = new Order([
        'status' => Order::STATUS_REFUNDED,
        'refunded' => true,
        'refunded_at' => now()->subDay(),
    ]);

    expect($order->refunded())->toBeTrue();
    expect($order->paid())->toBeFalse();
});

it('can determine if the order is for a specific product', function () {
    $order = new Order(['product_id' => '45067']);

    expect($order->hasProduct('45067'))->toBeTrue();
    expect($order->hasProduct('93048'))->toBeFalse();
});

it('can determine if the order is for a specific variant', function () {
    $order = new Order(['variant_id' => '45067']);

    expect($order->hasVariant('45067'))->toBeTrue();
    expect($order->hasVariant('93048'))->toBeFalse();
});

class Order extends LemonSqueezyOrder
{
    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'Y-m-d H:i:s';
}
