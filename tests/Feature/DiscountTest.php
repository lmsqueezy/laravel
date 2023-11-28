<?php

use Illuminate\Support\Carbon;
use LemonSqueezy\Laravel\Discount;
use LemonSqueezy\Laravel\DiscountRedemption;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

it('checks if discount is inactive before start date', function () {
    $futureDiscount = new Discount(['starts_at' => now()->addDays(1), 'status' => Discount::STATUS_PUBLISHED]);
    expect($futureDiscount->isActive())->toBeFalse();
});

it('checks if discount is inactive after end date', function () {
    $expiredDiscount = new Discount(['expires_at' => now()->subDay(), 'status' => Discount::STATUS_PUBLISHED]);
    expect($expiredDiscount->isActive())->toBeFalse();
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

    $calculatedAmount = $discount->calculateDiscountAmount(1000);

    expect($calculatedAmount)->toBe(100);
});

it('can calculate discount amount for fixed type', function () {
    $discount = Discount::factory()->create([
        'amount' => 200,
        'amount_type' => 'fixed',
    ]);

    $calculatedAmount = $discount->calculateDiscountAmount(1000);

    expect($calculatedAmount)->toBe(200);
});

it('can check if a discount is expired', function () {
    $expiredDiscount = Discount::factory()->expiredDiscount()->create();
    $activeDiscount = Discount::factory()->activeDiscount()->create();

    expect($expiredDiscount->isExpired())->toBeTrue();
    expect($activeDiscount->isExpired())->toBeFalse();
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

it('validates discount code length', function () {
    $shortCode = 'AB';
    $longCode = str_repeat('A', 257);
    $validCode = 'ABC123';
    $discount = new Discount();

    expect($discount->isValidCode($shortCode))->toBeFalse();
    expect($discount->isValidCode($longCode))->toBeFalse();
    expect($discount->isValidCode($validCode))->toBeTrue();
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

it('checks if discount has reached max redemptions', function () {
    $discount = Discount::factory()->create([
        'is_limited_redemptions' => true,
        'max_redemptions' => 1,
    ]);

    DiscountRedemption::factory()->create(['discount_id' => $discount->id]);

    expect($discount->hasReachedMaxRedemptions())->toBeTrue();
});

it('checks if once duration discount becomes inactive after one redemption', function () {
    $onceDiscount = Discount::factory()->create([
        'duration' => 'once',
        'status' => Discount::STATUS_PUBLISHED
    ]);

    // Simulate a redemption
    DiscountRedemption::factory()->create(['discount_id' => $onceDiscount->id]);

    expect($onceDiscount->refresh()->isActive())->toBeFalse();
});

it('checks if repeating duration discount remains active for specified months', function () {
    $startDate = now()->subMinutes(10);
    Carbon::setTestNow($startDate);

    $repeatingDiscount = Discount::factory()->create([
        'duration' => 'repeating',
        'duration_in_months' => 3,
        'max_redemptions' => 5,
        'is_limited_to_products' => false,
        'is_limited_redemptions' => false,
        'status' => Discount::STATUS_PUBLISHED,
        'starts_at' => $startDate,
        'expires_at' => null,
    ]);

    foreach (range(1, 3) as $month) {
        Carbon::setTestNow($startDate->copy()->addMonths($month));
        DiscountRedemption::factory()->create(['discount_id' => $repeatingDiscount->id]);
    }

    expect($repeatingDiscount->refresh()->isActive())->toBeTrue();

    Carbon::setTestNow($startDate->copy()->addMonths(4));
    expect($repeatingDiscount->refresh()->isActive())->toBeFalse();
});

it('checks if forever duration discount always remains active', function () {
    $foreverDiscount = Discount::factory()->create([
        'duration' => 'forever',
        'status' => Discount::STATUS_PUBLISHED
    ]);

    // Simulate multiple redemptions
    foreach (range(1, 5) as $i) {
        DiscountRedemption::factory()->create(['discount_id' => $foreverDiscount->id]);
    }

    expect($foreverDiscount->refresh()->isActive())->toBeTrue();
});