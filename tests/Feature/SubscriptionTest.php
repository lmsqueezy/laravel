<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use LemonSqueezy\Laravel\Subscription;

uses(RefreshDatabase::class);

it('can generate a checkout for a billable', function () {
    $subscription = Subscription::factory()->create();

    config()->set('lemon-squeezy.api_key', 'fake');

    Http::fake([
        'api.lemonsqueezy.com/*' => Http::response(['data' => ['attributes' => [
            'status' => Subscription::STATUS_ACTIVE,
            'product_id' => '12345',
            'variant_id' => '67890',
        ]]]),
    ]);

    $subscription = $subscription->swap('12345', '67890');

    expect($subscription)->toMatchArray(['product_id' => '12345', 'variant_id' => '67890']);
});

it('can determine if the subscription is valid while on its grace period', function () {
    $subscription = Subscription::factory()->cancelled()->create([
        'ends_at' => now()->addDays(5),
    ]);

    expect($subscription->valid())->toBeTrue();

    $subscription = Subscription::factory()->cancelled()->create([
        'ends_at' => now()->subDays(5),
    ]);

    expect($subscription->valid())->toBeFalse();
});
