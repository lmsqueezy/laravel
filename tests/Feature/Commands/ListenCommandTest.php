<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;

it('can call the listen command', function () {
    config()->set([
        'lemon-squeezy.api_key' => 'fake',
        'lemon-squeezy.signing_secret' => 'fake',
        'lemon-squeezy.store' => '123',
    ]);

    expect(Artisan::call('lmsqueezy:listen', ['service' => 'test']))->toEqual(Command::SUCCESS);
});

it('can validate services', function () {
    $this->expectException(ValidationException::class);

    Artisan::call('lmsqueezy:listen', ['service' => 'invalid']);
});
