<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use LemonSqueezy\Laravel\DiscountRedemption;
use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\Order;

uses(RefreshDatabase::class);

it('can create a discount redemption', function () {
    $discount = Discount::factory()->create();
    $order = new Order(['id' => 1]);
    
    $redemption = DiscountRedemption::factory()->create([
        'discount_id' => $discount->id,
        'order_id' => $order->id,
    ]);

    expect($redemption->discount_id)->toBe($discount->id);
    expect($redemption->order_id)->toBe($order->id);
});

it('can retrieve a discount redemption', function () {
    $redemption = DiscountRedemption::factory()->create();

    $foundRedemption = DiscountRedemption::find($redemption->id);

    expect($foundRedemption->is($redemption))->toBeTrue();
});

it('belongs to a discount', function () {
    $discount = Discount::factory()->create();
    $order = new Order(['id' => 1]);

    $redemption = DiscountRedemption::factory()->create([
        'discount_id' => $discount->id,
        'order_id' => $order->id,
    ]);

    expect($redemption->discount->is($discount))->toBeTrue();
});