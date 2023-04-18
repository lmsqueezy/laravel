<?php

use LemonSqueezy\Laravel\Subscription;

it('can determine if the subscription is on trial', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_ON_TRIAL]);

    expect($subscription->onTrial())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is active', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_ACTIVE]);

    expect($subscription->active())->toBeTrue();
    expect($subscription->cancelled())->toBeFalse();
});

it('can determine if the subscription is paused', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_PAUSED]);

    expect($subscription->paused())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is past due', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_PAST_DUE]);

    expect($subscription->pastDue())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is unpaid', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_UNPAID]);

    expect($subscription->unpaid())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is cancelled', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_CANCELLED]);

    expect($subscription->cancelled())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is expired', function () {
    $subscription = new Subscription(['status' => Subscription::STATUS_EXPIRED]);

    expect($subscription->expired())->toBeTrue();
    expect($subscription->active())->toBeFalse();
});

it('can determine if the subscription is on a specific product', function () {
    $subscription = new Subscription(['product_id' => '45067']);

    expect($subscription->hasProduct('45067'))->toBeTrue();
    expect($subscription->hasProduct('93048'))->toBeFalse();
});

it('can determine if the subscription is on a specific variant', function () {
    $subscription = new Subscription(['variant_id' => '45067']);

    expect($subscription->hasVariant('45067'))->toBeTrue();
    expect($subscription->hasVariant('93048'))->toBeFalse();
});
