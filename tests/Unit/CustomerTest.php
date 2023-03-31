<?php

use LaravelLemonSqueezy\Customer;

it('can determine if the customer is on a generic trial', function () {
    $customer = new Customer();
    $customer->setDateFormat('Y-m-d H:i:s');
    $customer->trial_ends_at = now()->addDays(7);

    expect($customer->onGenericTrial())->toBeTrue();
});

it('can determine if the customer has an expired generic trial', function () {
    $customer = new Customer();
    $customer->setDateFormat('Y-m-d H:i:s');
    $customer->trial_ends_at = now()->subDays(7);

    expect($customer->hasExpiredGenericTrial())->toBeTrue();
});
