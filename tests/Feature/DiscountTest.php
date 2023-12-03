<?php

use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Customer;
use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\DiscountRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\User;

uses(RefreshDatabase::class);

it('can create a discount', function () {
    $discount = Discount::factory()->create([
        'code' => 'TESTCODE',
        'amount' => 500,
        'amount_type' => 'fixed',
        'status' => 'published',
    ]);

    expect($discount->code)->toBe('TESTCODE');
    expect($discount->amount)->toBe(500);
    expect($discount->amount_type)->toBe('fixed');
    expect($discount->status)->toBe('published');
});

it('can apply a discount to an order', function () {
    $discount = Discount::factory()->create([
        'status' => 'published',
    ]);

    $order = Order::factory()->create([
        'identifier' => env('LEMON_SQUEEZY_STORE'),
    ]);

    $order->applyDiscount($discount);

    $expectedDiscountTotal = $discount->calculateDiscountAmount($order->subtotal);
    expect($order->discount_total)->toBe($expectedDiscountTotal);
});

it('can calculate a percentage discount amount', function () {
    $discount = Discount::factory()->create([
        'amount' => 10,
        'amount_type' => 'percent',
    ]);

    $discountAmount = $discount->calculateDiscountAmount(1000);

    expect($discountAmount)->toBe(100);
});

it('can calculate a fixed discount amount', function () {
    $discount = Discount::factory()->create([
        'amount' => 500,
        'amount_type' => 'fixed',
    ]);

    $discountAmount = $discount->calculateDiscountAmount(1000);

    expect($discountAmount)->toBe(500);
});

it('can check if a discount is active', function () {
    $discount = Discount::factory()->create([
        'status' => 'published',
        'starts_at' => now()->subDay(),
        'expires_at' => now()->addDay(),
    ]);

    expect($discount->isActive())->toBeTrue();
});

it('can check if a discount is expired', function () {
    $discount = Discount::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    expect($discount->isExpired())->toBeTrue();
});

it('can increment the redemptions count', function () {
    $discount = Discount::factory()->create();
    $order = Order::factory()->create([
        'identifier' => env('LEMON_SQUEEZY_STORE'),
    ]);

    $discount->incrementRedemptions($order->id);

    expect($discount->redemptions()->count())->toBe(1);
});

it('can check if a discount has reached the maximum redemptions', function () {
    $discount = Discount::factory()->create([
        'is_limited_redemptions' => true,
        'max_redemptions' => 1,
    ]);

    $order = Order::factory()->create([
        'identifier' => env('LEMON_SQUEEZY_STORE'),
    ]);

    $discount->incrementRedemptions($order->id);

    expect($discount->hasReachedMaxRedemptions())->toBeTrue();
});