<?php

use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\DiscountRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a discount', function () {
    $discount = Discount::factory()->create([
        'code' => 'TESTCODE',
        'amount' => 500, // Assuming this is in cents
        'amount_type' => 'fixed',
        'status' => 'published',
    ]);

    expect($discount->code)->toBe('TESTCODE');
    expect($discount->amount)->toBe(500);
    expect($discount->amount_type)->toBe('fixed');
    expect($discount->status)->toBe('published');
});

it('can determine if the discount is active', function () {
    $activeDiscount = new Discount(['status' => Discount::STATUS_PUBLISHED, 'expires_at' => now()->addDays(1)]);
    $inactiveDiscount = new Discount(['status' => Discount::STATUS_DRAFT]);

    expect($activeDiscount->isActive())->toBeTrue();
    expect($inactiveDiscount->isActive())->toBeFalse();
});

it('can calculate discount amount for percent type', function () {
    $discount = Discount::factory()->create([
        'amount' => 10, // 10%
        'amount_type' => 'percent',
    ]);

    $calculatedAmount = $discount->calculateDiscountAmount(1000); // Assuming original amount is 1000 cents

    expect($calculatedAmount)->toBe(100); // 10% of 1000 cents
});

it('can calculate discount amount for fixed type', function () {
    $discount = Discount::factory()->create([
        'amount' => 200, // Fixed amount in cents
        'amount_type' => 'fixed',
    ]);

    $calculatedAmount = $discount->calculateDiscountAmount(1000); // Assuming original amount is 1000 cents

    expect($calculatedAmount)->toBe(200); // Fixed discount of 200 cents
});

it('can check if a discount is expired', function () {
    $expiredDiscount = Discount::factory()->expiredDiscount()->create();
    $activeDiscount = Discount::factory()->activeDiscount()->create();

    expect($expiredDiscount->isExpired())->toBeTrue();
    expect($activeDiscount->isExpired())->toBeFalse();
});

it('can check if discount has reached max redemptions', function () {
    // Create a discount instance
    $discount = new Discount([
        'is_limited_redemptions' => true,
        'max_redemptions' => 1,
    ]);

    // Manually set the discount ID as it would be in a database
    $discount->id = 1;

    // Create a DiscountRedemption instance and associate it with the discount
    $redemption = new DiscountRedemption(['discount_id' => $discount->id]);
    
    // Mock the redemptions relationship to return the created redemption
    $discount->setRelation('redemptions', collect([$redemption]));
    
    // Assert that the discount has reached its max redemptions
    expect($discount->hasReachedMaxRedemptions())->toBeTrue();
});

it('can determine if the discount is expired', function () {
    $expiredDiscount = new Discount(['expires_at' => now()->subDay()]);
    $notExpiredDiscount = new Discount(['expires_at' => now()->addDay()]);

    expect($expiredDiscount->isExpired())->toBeTrue();
    expect($notExpiredDiscount->isExpired())->toBeFalse();
});

it('can validate discount code format', function () {
    $validCode = 'ABC123';
    $invalidCode = 'abc';
    $discount = new Discount();

    expect($discount->isValidCode($validCode))->toBeTrue();
    expect($discount->isValidCode($invalidCode))->toBeFalse();
});

it('can calculate fixed discount amount', function () {
    $discount = new Discount(['amount' => 500, 'amount_type' => 'fixed']);

    $calculatedAmount = $discount->calculateDiscountAmount(1000);

    expect($calculatedAmount)->toBe(500);
});

it('can calculate percent discount amount', function () {
    $discount = new Discount(['amount' => 10, 'amount_type' => 'percent']);

    $calculatedAmount = $discount->calculateDiscountAmount(1000);

    expect($calculatedAmount)->toBe(100);
});